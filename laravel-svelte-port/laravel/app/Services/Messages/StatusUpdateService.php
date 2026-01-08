<?php

namespace App\Services\Messages;

use App\Models\Message;
use Illuminate\Support\Facades\Log;

class StatusUpdateService
{
    public function __construct(
        private Message $message,
        private string $status,
        private ?string $externalError = null
    ) {}

    /**
     * Perform the status update
     */
    public function perform(): bool
    {
        if (!$this->isValidStatusTransition()) {
            return false;
        }

        return $this->updateMessageStatus();
    }

    /**
     * Update the message status
     */
    private function updateMessageStatus(): bool
    {
        try {
            $this->message->update([
                'status' => $this->getStatusValue(),
                'external_error' => $this->status === 'failed' ? $this->externalError : null,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update message status', [
                'message_id' => $this->message->id,
                'status' => $this->status,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if the status transition is valid
     */
    private function isValidStatusTransition(): bool
    {
        $validStatuses = ['sent', 'delivered', 'read', 'failed'];
        
        if (!in_array($this->status, $validStatuses)) {
            return false;
        }

        // Don't allow changing from 'read' to 'delivered'
        if ($this->message->status === Message::STATUS_READ && $this->status === 'delivered') {
            return false;
        }

        return true;
    }

    /**
     * Get the numeric status value from string
     */
    private function getStatusValue(): int
    {
        return match ($this->status) {
            'sent' => Message::STATUS_SENT,
            'delivered' => Message::STATUS_DELIVERED,
            'read' => Message::STATUS_READ,
            'failed' => Message::STATUS_FAILED,
            default => Message::STATUS_SENT,
        };
    }
}