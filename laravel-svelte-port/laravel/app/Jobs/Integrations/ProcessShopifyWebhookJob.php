<?php

namespace App\Jobs\Integrations;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Integration;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Inbox;
use App\Services\Integrations\ShopifyService;

class ProcessShopifyWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $topic,
        public array $payload,
        public ?int $integrationId = null
    ) {}

    public function handle(): void
    {
        try {
            Log::info('Processing Shopify webhook', [
                'topic' => $this->topic,
                'integration_id' => $this->integrationId,
            ]);

            if (!$this->integrationId) {
                Log::warning('Shopify webhook missing integration ID', ['topic' => $this->topic]);
                return;
            }

            $integration = Integration::find($this->integrationId);
            if (!$integration) {
                Log::error('Shopify integration not found', ['integration_id' => $this->integrationId]);
                return;
            }

            $service = new ShopifyService($integration);

            // Process different webhook topics
            match ($this->topic) {
                'orders/create', 'orders/updated' => $this->handleOrderEvent($service, $integration),
                'customers/create', 'customers/update' => $this->handleCustomerEvent($service, $integration),
                'app/uninstalled' => $this->handleAppUninstalled($integration),
                default => Log::info('Unhandled Shopify webhook topic', ['topic' => $this->topic]),
            };
        } catch (\Throwable $e) {
            Log::error('Failed processing Shopify webhook', [
                'error' => $e->getMessage(),
                'topic' => $this->topic,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle order-related webhook events
     */
    protected function handleOrderEvent(ShopifyService $service, Integration $integration): void
    {
        try {
            $orderResult = $service->processOrder($this->payload);
            if (!$orderResult) {
                Log::warning('Shopify order processing returned null', [
                    'order_id' => data_get($this->payload, 'id'),
                ]);
                return;
            }

            $contactData = $orderResult['contact'];
            $orderSummary = $orderResult['order_summary'];

            // Find or create contact
            $contact = $this->findOrCreateContact($integration, $contactData);
            if (!$contact) {
                Log::error('Failed to create contact for Shopify order', [
                    'order_id' => $orderSummary['order_id'],
                ]);
                return;
            }

            // Create or update conversation
            $conversation = $this->findOrCreateConversation($integration, $contact, $orderSummary);
            if (!$conversation) {
                Log::error('Failed to create conversation for Shopify order', [
                    'order_id' => $orderSummary['order_id'],
                ]);
                return;
            }

            // Create message about the order
            $this->createOrderMessage($conversation, $orderSummary);

            Log::info('Shopify order processed successfully', [
                'order_id' => $orderSummary['order_id'],
                'contact_id' => $contact->id,
                'conversation_id' => $conversation->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Shopify order event processing failed', [
                'order_id' => data_get($this->payload, 'id'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle customer-related webhook events
     */
    protected function handleCustomerEvent(ShopifyService $service, Integration $integration): void
    {
        try {
            $contactData = $service->syncCustomer($this->payload);
            if (!$contactData) {
                Log::warning('Shopify customer sync returned null', [
                    'customer_id' => data_get($this->payload, 'id'),
                ]);
                return;
            }

            $contact = $this->findOrCreateContact($integration, $contactData);
            if ($contact) {
                Log::info('Shopify customer synced successfully', [
                    'customer_id' => data_get($this->payload, 'id'),
                    'contact_id' => $contact->id,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Shopify customer event processing failed', [
                'customer_id' => data_get($this->payload, 'id'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle app uninstalled event
     */
    protected function handleAppUninstalled(Integration $integration): void
    {
        try {
            // Disable the integration
            $integration->update(['active' => false]);
            
            Log::info('Shopify app uninstalled, integration disabled', [
                'integration_id' => $integration->id,
                'shop_domain' => data_get($integration->settings, 'shop_domain'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to handle Shopify app uninstall', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Find or create contact from Shopify customer data
     */
    protected function findOrCreateContact(Integration $integration, array $contactData): ?Contact
    {
        try {
            $account = $integration->account;
            if (!$account) {
                Log::error('Integration missing account', ['integration_id' => $integration->id]);
                return null;
            }

            // Try to find existing contact by email or phone
            $contact = null;
            if (!empty($contactData['email'])) {
                $contact = Contact::where('account_id', $account->id)
                    ->where('email', $contactData['email'])
                    ->first();
            }

            if (!$contact && !empty($contactData['phone_number'])) {
                $contact = Contact::where('account_id', $account->id)
                    ->where('phone_number', $contactData['phone_number'])
                    ->first();
            }

            // Create new contact if not found
            if (!$contact) {
                $contact = Contact::create([
                    'account_id' => $account->id,
                    'name' => $contactData['name'] ?? 'Shopify Customer',
                    'email' => $contactData['email'] ?? null,
                    'phone_number' => $contactData['phone_number'] ?? null,
                    'additional_attributes' => $contactData['additional_attributes'] ?? [],
                ]);
            } else {
                // Update existing contact with new data
                $contact->update([
                    'name' => $contactData['name'] ?? $contact->name,
                    'email' => $contactData['email'] ?? $contact->email,
                    'phone_number' => $contactData['phone_number'] ?? $contact->phone_number,
                    'additional_attributes' => array_merge(
                        $contact->additional_attributes ?? [],
                        $contactData['additional_attributes'] ?? []
                    ),
                ]);
            }

            return $contact;
        } catch (\Throwable $e) {
            Log::error('Failed to find or create contact', [
                'error' => $e->getMessage(),
                'contact_data' => $contactData,
            ]);
            return null;
        }
    }

    /**
     * Find or create conversation for the contact
     */
    protected function findOrCreateConversation(Integration $integration, Contact $contact, array $orderSummary): ?Conversation
    {
        try {
            $account = $integration->account;
            if (!$account) {
                return null;
            }

            // Find a Shopify inbox for this account
            $inbox = Inbox::where('account_id', $account->id)
                ->where('channel_type', 'Channel::Api') // Assuming Shopify uses API channel
                ->first();

            if (!$inbox) {
                // Create a default Shopify inbox if none exists
                $inbox = Inbox::create([
                    'account_id' => $account->id,
                    'name' => 'Shopify',
                    'channel_type' => 'Channel::Api',
                    'channel_id' => null, // Will be set when channel is created
                ]);
            }

            // Look for existing conversation with this contact
            $conversation = Conversation::where('account_id', $account->id)
                ->where('inbox_id', $inbox->id)
                ->where('contact_id', $contact->id)
                ->where('status', '!=', \App\Models\Conversation::STATUS_RESOLVED)
                ->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'account_id' => $account->id,
                    'inbox_id' => $inbox->id,
                    'contact_id' => $contact->id,
                    'status' => \App\Models\Conversation::STATUS_OPEN,
                    'additional_attributes' => [
                        'shopify_order_id' => $orderSummary['order_id'],
                        'shopify_order_number' => $orderSummary['order_number'],
                    ],
                ]);
            }

            return $conversation;
        } catch (\Throwable $e) {
            Log::error('Failed to find or create conversation', [
                'error' => $e->getMessage(),
                'contact_id' => $contact->id,
            ]);
            return null;
        }
    }

    /**
     * Create a message about the order
     */
    protected function createOrderMessage(Conversation $conversation, array $orderSummary): void
    {
        try {
            $messageContent = $this->formatOrderMessage($orderSummary);

            Message::create([
                'account_id' => $conversation->account_id,
                'inbox_id' => $conversation->inbox_id,
                'conversation_id' => $conversation->id,
                'message_type' => 'incoming',
                'content_type' => 'text',
                'content' => $messageContent,
                'source_id' => $orderSummary['order_id'],
                'additional_attributes' => [
                    'shopify_order_data' => $orderSummary,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to create order message', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversation->id,
            ]);
        }
    }

    /**
     * Format order information into a readable message
     */
    protected function formatOrderMessage(array $orderSummary): string
    {
        $status = match ($this->topic) {
            'orders/create' => 'New order created',
            'orders/updated' => 'Order updated',
            'orders/paid' => 'Order payment received',
            'orders/cancelled' => 'Order cancelled',
            'orders/fulfilled' => 'Order fulfilled',
            default => 'Order event',
        };

        return sprintf(
            "%s\n\nOrder #%s\nTotal: %s %s\nFinancial Status: %s\nFulfillment Status: %s\n\nView in Shopify: %s",
            $status,
            $orderSummary['order_number'],
            $orderSummary['total_price'],
            $orderSummary['currency'],
            ucfirst($orderSummary['financial_status']),
            ucfirst($orderSummary['fulfillment_status'] ?? 'unfulfilled'),
            $orderSummary['admin_url']
        );
    }
}
