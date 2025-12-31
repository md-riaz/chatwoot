<?php

namespace App\Services\Channels;

use App\Actions\Contact\CreateContactAction;
use App\Actions\Conversation\CreateConversationAction;
use App\Actions\Message\CreateMessageAction;
use App\Data\Channels\InboundMessageData;
use App\Data\Contact\ContactData;
use App\Data\Conversation\ConversationData;
use App\Data\Message\MessageData;
use App\Models\Contact;
use App\Models\ContactInbox;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\Message;
use App\Repositories\Contact\ContactRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InboundMessageService
{
    public function __construct(
        private ContactRepository $contactRepository
    ) {}

    public function ingest(InboundMessageData $data): Message
    {
        $inbox = Inbox::findOrFail($data->inbox_id);

        $contact = $this->findOrCreateContact($data);
        $contactInbox = $this->ensureContactInbox($contact, $inbox, $data->provider_contact_id);

        $conversation = $this->findOrCreateConversation($data, $contact, $contactInbox);

        if ($data->external_source_id && Message::where('external_source_id', $data->external_source_id)->exists()) {
            return Message::where('external_source_id', $data->external_source_id)->first();
        }

        $messageData = new MessageData(
            id: null,
            account_id: $data->account_id,
            conversation_id: $conversation->id,
            inbox_id: $inbox->id,
            sender_id: $contact->id,
            sender_type: Contact::class,
            message_type: Message::TYPE_INCOMING,
            content: $data->content,
            content_type: $data->content_type,
            content_attributes: $data->metadata,
            private: false,
            external_source_id: $data->external_source_id
        );

        $message = CreateMessageAction::run($messageData);

        $this->attachMetadata($message, $data->metadata);
        $this->storeAttachments($message, $data->attachments);

        return $message;
    }

    private function findOrCreateContact(InboundMessageData $data): Contact
    {
        $existing = $this->contactRepository->findByIdentifier($data->account_id, $data->contact_identifier);
        if ($existing) {
            return $existing;
        }

        $contactData = new ContactData(
            id: null,
            account_id: $data->account_id,
            name: $data->contact_name,
            email: $data->contact_email,
            phone_number: $data->contact_phone,
            identifier: $data->contact_identifier,
            avatar_url: null,
            custom_attributes: [],
            additional_attributes: [
                'source' => $data->contact_source,
            ]
        );

        return CreateContactAction::run($contactData);
    }

    private function ensureContactInbox(Contact $contact, Inbox $inbox, ?string $providerContactId = null): ContactInbox
    {
        $contactInbox = ContactInbox::firstOrCreate(
            [
                'contact_id' => $contact->id,
                'inbox_id' => $inbox->id,
            ],
            [
                'source_id' => $providerContactId,
            ]
        );

        if ($providerContactId && $contactInbox->source_id !== $providerContactId) {
            $contactInbox->update(['source_id' => $providerContactId]);
        }

        return $contactInbox;
    }

    private function findOrCreateConversation(InboundMessageData $data, Contact $contact, ContactInbox $contactInbox): Conversation
    {
        if ($data->conversation_id) {
            return Conversation::findOrFail($data->conversation_id);
        }

        $existing = Conversation::where('account_id', $data->account_id)
            ->where('inbox_id', $data->inbox_id)
            ->where('contact_id', $contact->id)
            ->where('status', Conversation::STATUS_OPEN)
            ->first();

        if ($existing) {
            return $existing;
        }

        $conversationData = new ConversationData(
            id: null,
            account_id: $data->account_id,
            inbox_id: $data->inbox_id,
            contact_id: $contact->id,
            contact_inbox_id: $contactInbox->id,
            assignee_id: null,
            team_id: null,
            display_id: 0,
            status: Conversation::STATUS_OPEN,
            priority: Conversation::PRIORITY_NONE,
            custom_attributes: [],
            snoozed_until: null
        );

        return CreateConversationAction::run($conversationData);
    }

    private function attachMetadata(Message $message, array $metadata): void
    {
        if (empty($metadata)) {
            return;
        }

        try {
            $message->update([
                'content_attributes' => array_merge($message->content_attributes ?? [], ['metadata' => $metadata]),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to attach metadata to inbound message', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function storeAttachments(Message $message, array $attachments): void
    {
        if (empty($attachments)) {
            return;
        }

        foreach ($attachments as $attachment) {
            try {
                $url = $attachment['url'] ?? null;
                $content = $attachment['content'] ?? null;
                $contentType = $attachment['content_type'] ?? null;
                $filename = $attachment['filename'] ?? $attachment['name'] ?? null;

                if (! $content && $url) {
                    $resp = Http::get($url);
                    if (! $resp->successful()) {
                        Log::warning('Inbound attachment download failed', ['url' => $url, 'status' => $resp->status()]);
                        continue;
                    }
                    $content = $resp->body();
                    $contentType = $contentType ?? $resp->header('Content-Type');
                    $filename = $filename ?? basename(parse_url($url, PHP_URL_PATH));
                }

                if (! $content) {
                    continue;
                }

                $filename = $filename ?: 'attachment';
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $safeName = Str::slug(pathinfo($filename, PATHINFO_FILENAME));
                $path = "attachments/{$message->account_id}/{$safeName}-" . time() . ($ext ? ".{$ext}" : '');

                Storage::disk('public')->put($path, $content);
                $dataUrl = Storage::url($path);

                \App\Models\Attachment::create([
                    'file_type' => $this->inferFileType($contentType),
                    'file_name' => $filename,
                    'account_id' => $message->account_id,
                    'message_id' => $message->id,
                    'external_url' => $url,
                    'content_type' => $contentType,
                    'meta' => $attachment,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Inbound attachment store failed', ['message_id' => $message->id, 'error' => $e->getMessage()]);
            }
        }
    }

    private function inferFileType(?string $contentType): int
    {
        return match (true) {
            str_starts_with((string) $contentType, 'image/') => \App\Models\Attachment::TYPE_IMAGE,
            str_starts_with((string) $contentType, 'audio/') => \App\Models\Attachment::TYPE_AUDIO,
            str_starts_with((string) $contentType, 'video/') => \App\Models\Attachment::TYPE_VIDEO,
            default => \App\Models\Attachment::TYPE_FILE,
        };
    }
}
