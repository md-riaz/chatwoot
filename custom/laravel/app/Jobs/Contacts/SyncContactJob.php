<?php

namespace App\Jobs\Contacts;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Contact;
use App\Models\Company;
use App\Jobs\Webhooks\SendWebhooksJob;

class SyncContactJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $contactId)
    {
    }

    public function handle(): void
    {
        try {
            $contact = Contact::find($this->contactId);
            if (! $contact) {
                return;
            }

            $changed = false;

            // Normalize email
            if (! empty($contact->email)) {
                $normalized = strtolower(trim($contact->email));
                if ($normalized !== $contact->email) {
                    $contact->email = $normalized;
                    $changed = true;
                }
            }

            // Normalize phone number (digits only)
            if (! empty($contact->phone_number)) {
                $normalizedPhone = preg_replace('/\D+/', '', $contact->phone_number);
                if ($normalizedPhone !== $contact->phone_number) {
                    $contact->phone_number = $normalizedPhone;
                    $changed = true;
                }
            }

            // Associate company if provided in custom attributes
            $companyName = data_get($contact->custom_attributes, 'company');
            if ($companyName && ! $contact->company_id) {
                $company = Company::firstOrCreate(
                    ['account_id' => $contact->account_id, 'domain' => null, 'name' => $companyName],
                    ['name' => $companyName, 'account_id' => $contact->account_id]
                );
                if ($company && $company->id) {
                    $contact->company_id = $company->id;
                    $changed = true;
                }
            }

            if ($changed) {
                $contact->save();
            }

            // Emit an internal webhook to inform subscribers that contact was synced
            SendWebhooksJob::dispatch($contact->account_id, 'contact_synced', ['contact_id' => $contact->id]);

            Log::info('SyncContactJob completed', ['contact_id' => $this->contactId]);
        } catch (\Throwable $e) {
            Log::error('SyncContactJob failed', ['contact_id' => $this->contactId, 'error' => $e->getMessage()]);
        }
    }
}
