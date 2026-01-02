<?php

namespace App\Services\Email;

use App\Models\Contact;
use App\Models\Account;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BounceHandlingService
{
    const BOUNCE_TYPE_HARD = 'hard';
    const BOUNCE_TYPE_SOFT = 'soft';
    const BOUNCE_TYPE_COMPLAINT = 'complaint';

    /**
     * Process bounce webhook from email service provider.
     */
    public function processBounceWebhook(array $bounceData): array
    {
        try {
            $processedBounce = $this->parseBounceData($bounceData);
            
            $contact = $this->findContactByEmail($processedBounce['email']);
            
            if (!$contact) {
                Log::warning('Bounce received for unknown contact', [
                    'email' => $processedBounce['email'],
                    'bounce_type' => $processedBounce['type'],
                ]);
                
                return ['success' => false, 'reason' => 'Contact not found'];
            }

            $this->handleBounce($contact, $processedBounce);

            return ['success' => true, 'contact_id' => $contact->id];
        } catch (\Exception $e) {
            Log::error('Bounce processing failed', [
                'error' => $e->getMessage(),
                'bounce_data' => $bounceData,
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Parse bounce data from different providers.
     */
    protected function parseBounceData(array $bounceData): array
    {
        // Handle different email service providers
        if (isset($bounceData['Type']) && $bounceData['Type'] === 'Notification') {
            // AWS SES format
            return $this->parseSESBounce($bounceData);
        } elseif (isset($bounceData['event-data'])) {
            // Mailgun format
            return $this->parseMailgunBounce($bounceData);
        } elseif (isset($bounceData['event'])) {
            // SendGrid format
            return $this->parseSendGridBounce($bounceData);
        } else {
            // Generic format
            return $this->parseGenericBounce($bounceData);
        }
    }

    /**
     * Parse AWS SES bounce notification.
     */
    protected function parseSESBounce(array $data): array
    {
        $message = json_decode($data['Message'], true);
        $bounce = $message['bounce'] ?? $message['complaint'] ?? [];
        
        $bounceType = isset($message['complaint']) ? self::BOUNCE_TYPE_COMPLAINT : 
                     ($bounce['bounceType'] === 'Permanent' ? self::BOUNCE_TYPE_HARD : self::BOUNCE_TYPE_SOFT);

        $recipients = $bounce['bouncedRecipients'] ?? $bounce['complainedRecipients'] ?? [];
        $email = $recipients[0]['emailAddress'] ?? '';

        return [
            'email' => $email,
            'type' => $bounceType,
            'reason' => $bounce['bounceSubType'] ?? $recipients[0]['diagnosticCode'] ?? 'Unknown',
            'timestamp' => $bounce['timestamp'] ?? now(),
            'provider' => 'ses',
        ];
    }

    /**
     * Parse Mailgun bounce notification.
     */
    protected function parseMailgunBounce(array $data): array
    {
        $eventData = $data['event-data'];
        
        $bounceType = match ($eventData['event']) {
            'failed' => $eventData['severity'] === 'permanent' ? self::BOUNCE_TYPE_HARD : self::BOUNCE_TYPE_SOFT,
            'complained' => self::BOUNCE_TYPE_COMPLAINT,
            default => self::BOUNCE_TYPE_SOFT,
        };

        return [
            'email' => $eventData['recipient'],
            'type' => $bounceType,
            'reason' => $eventData['reason'] ?? $eventData['description'] ?? 'Unknown',
            'timestamp' => Carbon::createFromTimestamp($eventData['timestamp']),
            'provider' => 'mailgun',
        ];
    }

    /**
     * Parse SendGrid bounce notification.
     */
    protected function parseSendGridBounce(array $data): array
    {
        $bounceType = match ($data['event']) {
            'bounce' => $data['type'] === 'bounce' ? self::BOUNCE_TYPE_HARD : self::BOUNCE_TYPE_SOFT,
            'spamreport' => self::BOUNCE_TYPE_COMPLAINT,
            default => self::BOUNCE_TYPE_SOFT,
        };

        return [
            'email' => $data['email'],
            'type' => $bounceType,
            'reason' => $data['reason'] ?? 'Unknown',
            'timestamp' => Carbon::createFromTimestamp($data['timestamp']),
            'provider' => 'sendgrid',
        ];
    }

    /**
     * Parse generic bounce format.
     */
    protected function parseGenericBounce(array $data): array
    {
        return [
            'email' => $data['email'] ?? $data['recipient'] ?? '',
            'type' => $data['bounce_type'] ?? $data['type'] ?? self::BOUNCE_TYPE_SOFT,
            'reason' => $data['reason'] ?? $data['message'] ?? 'Unknown',
            'timestamp' => isset($data['timestamp']) ? Carbon::parse($data['timestamp']) : now(),
            'provider' => $data['provider'] ?? 'generic',
        ];
    }

    /**
     * Find contact by email address.
     */
    protected function findContactByEmail(string $email): ?Contact
    {
        return Contact::where('email', $email)->first();
    }

    /**
     * Handle bounce based on type and contact history.
     */
    protected function handleBounce(Contact $contact, array $bounceData): void
    {
        // Update contact bounce information
        $bounceInfo = $contact->additional_attributes['bounce_info'] ?? [];
        
        $bounceInfo['last_bounce_at'] = $bounceData['timestamp']->toISOString();
        $bounceInfo['last_bounce_type'] = $bounceData['type'];
        $bounceInfo['last_bounce_reason'] = $bounceData['reason'];
        $bounceInfo['bounce_provider'] = $bounceData['provider'];

        // Handle different bounce types
        switch ($bounceData['type']) {
            case self::BOUNCE_TYPE_HARD:
                $this->handleHardBounce($contact, $bounceInfo);
                break;
                
            case self::BOUNCE_TYPE_SOFT:
                $this->handleSoftBounce($contact, $bounceInfo);
                break;
                
            case self::BOUNCE_TYPE_COMPLAINT:
                $this->handleComplaint($contact, $bounceInfo);
                break;
        }

        // Update contact
        $contact->additional_attributes = array_merge(
            $contact->additional_attributes ?? [],
            ['bounce_info' => $bounceInfo]
        );
        $contact->save();

        // Log bounce event
        Log::info('Email bounce processed', [
            'contact_id' => $contact->id,
            'email' => $contact->email,
            'bounce_type' => $bounceData['type'],
            'reason' => $bounceData['reason'],
        ]);
    }

    /**
     * Handle hard bounce - immediately disable email.
     */
    protected function handleHardBounce(Contact $contact, array &$bounceInfo): void
    {
        $bounceInfo['hard_bounce_count'] = ($bounceInfo['hard_bounce_count'] ?? 0) + 1;
        
        if (config('email.bounce.auto_disable_on_hard_bounce', true)) {
            $bounceInfo['email_disabled'] = true;
            $bounceInfo['email_disabled_at'] = now()->toISOString();
            $bounceInfo['email_disabled_reason'] = 'Hard bounce';

            // Mark contact email as invalid
            $contact->email_status = 'invalid';
        }
        
        Log::warning('Contact email disabled due to hard bounce', [
            'contact_id' => $contact->id,
            'email' => $contact->email,
        ]);
    }

    /**
     * Handle soft bounce - track and disable after threshold.
     */
    protected function handleSoftBounce(Contact $contact, array &$bounceInfo): void
    {
        $maxSoftBounces = config('email.bounce.max_soft_bounces', 5);
        $resetDays = config('email.bounce.soft_bounce_reset_days', 30);
        
        $bounceInfo['soft_bounce_count'] = ($bounceInfo['soft_bounce_count'] ?? 0) + 1;
        
        // Reset soft bounce count if it's been too long since last bounce
        $lastBounceAt = isset($bounceInfo['last_soft_bounce_at']) ? 
            Carbon::parse($bounceInfo['last_soft_bounce_at']) : null;
            
        if ($lastBounceAt && $lastBounceAt->diffInDays(now()) > $resetDays) {
            $bounceInfo['soft_bounce_count'] = 1;
        }
        
        $bounceInfo['last_soft_bounce_at'] = now()->toISOString();

        // Disable email if soft bounce threshold exceeded
        if ($bounceInfo['soft_bounce_count'] >= $maxSoftBounces) {
            $bounceInfo['email_disabled'] = true;
            $bounceInfo['email_disabled_at'] = now()->toISOString();
            $bounceInfo['email_disabled_reason'] = 'Too many soft bounces';
            
            $contact->email_status = 'invalid';
            
            Log::warning('Contact email disabled due to soft bounce threshold', [
                'contact_id' => $contact->id,
                'email' => $contact->email,
                'soft_bounce_count' => $bounceInfo['soft_bounce_count'],
            ]);
        }
    }

    /**
     * Handle complaint - immediately disable email.
     */
    protected function handleComplaint(Contact $contact, array &$bounceInfo): void
    {
        $bounceInfo['complaint_count'] = ($bounceInfo['complaint_count'] ?? 0) + 1;
        
        if (config('email.bounce.auto_disable_on_complaint', true)) {
            $bounceInfo['email_disabled'] = true;
            $bounceInfo['email_disabled_at'] = now()->toISOString();
            $bounceInfo['email_disabled_reason'] = 'Spam complaint';

            // Mark contact email as invalid
            $contact->email_status = 'invalid';
        }
        
        Log::warning('Contact email disabled due to spam complaint', [
            'contact_id' => $contact->id,
            'email' => $contact->email,
        ]);
    }

    /**
     * Check if contact email is deliverable.
     */
    public function isEmailDeliverable(Contact $contact): bool
    {
        $bounceInfo = $contact->additional_attributes['bounce_info'] ?? [];
        
        return !($bounceInfo['email_disabled'] ?? false) && 
               $contact->email_status !== 'invalid';
    }

    /**
     * Re-enable email for contact (manual override).
     */
    public function reEnableEmail(Contact $contact, string $reason = 'Manual override'): void
    {
        $bounceInfo = $contact->additional_attributes['bounce_info'] ?? [];
        
        $bounceInfo['email_disabled'] = false;
        $bounceInfo['email_re_enabled_at'] = now()->toISOString();
        $bounceInfo['email_re_enabled_reason'] = $reason;
        
        $contact->additional_attributes = array_merge(
            $contact->additional_attributes ?? [],
            ['bounce_info' => $bounceInfo]
        );
        $contact->email_status = 'valid';
        $contact->save();

        Log::info('Contact email re-enabled', [
            'contact_id' => $contact->id,
            'email' => $contact->email,
            'reason' => $reason,
        ]);
    }

    /**
     * Get bounce statistics for account.
     */
    public function getBounceStatistics(Account $account): array
    {
        $contacts = $account->contacts()->get();
        
        $stats = [
            'total_contacts' => $contacts->count(),
            'valid_emails' => 0,
            'invalid_emails' => 0,
            'hard_bounces' => 0,
            'soft_bounces' => 0,
            'complaints' => 0,
        ];

        foreach ($contacts as $contact) {
            $bounceInfo = $contact->additional_attributes['bounce_info'] ?? [];
            
            if ($bounceInfo['email_disabled'] ?? false) {
                $stats['invalid_emails']++;
                
                if (($bounceInfo['hard_bounce_count'] ?? 0) > 0) {
                    $stats['hard_bounces']++;
                }
                
                if (($bounceInfo['complaint_count'] ?? 0) > 0) {
                    $stats['complaints']++;
                }
                
                if (($bounceInfo['soft_bounce_count'] ?? 0) >= self::MAX_SOFT_BOUNCES) {
                    $stats['soft_bounces']++;
                }
            } else {
                $stats['valid_emails']++;
            }
        }

        return $stats;
    }
}