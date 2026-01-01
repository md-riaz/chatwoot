<?php

namespace App\Services\Channels\Whatsapp;

use App\Services\Channels\BaseSendOnChannelService;
use App\Models\Message;
use App\Models\Channels\Whatsapp;

class SendOnWhatsappService extends BaseSendOnChannelService
{
    protected function channelClass(): string
    {
        return Whatsapp::class;
    }

    protected function performReply(): void
    {
        /** @var Whatsapp $channel */
        $channel = $this->channel();
        
        try {
            $phoneNumber = $this->getContactPhoneNumber();
            
            if (!$phoneNumber) {
                $this->message->update([
                    'status' => Message::STATUS_FAILED, 
                    'external_error' => 'Contact phone number not found'
                ]);
                return;
            }

            // Use the provider service to send the message
            $sourceId = $channel->sendMessage($phoneNumber, $this->message);
            
            if ($sourceId) {
                $this->message->update([
                    'source_id' => $sourceId,
                    'status' => Message::STATUS_SENT
                ]);
                $this->message->sendUpdateEvent();
            } else {
                $this->message->update([
                    'status' => Message::STATUS_FAILED,
                    'external_error' => 'Failed to send message via provider'
                ]);
            }

        } catch (\Exception $e) {
            report($e);
            $this->message->update([
                'status' => Message::STATUS_FAILED, 
                'external_error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get the contact's phone number for WhatsApp
     */
    private function getContactPhoneNumber(): ?string
    {
        $contactInbox = $this->conversation()->contactInbox;
        
        if (!$contactInbox) {
            return null;
        }

        // Try source_id first (WhatsApp phone number)
        if ($contactInbox->source_id) {
            return $contactInbox->source_id;
        }

        // Fallback to contact phone number
        $contact = $contactInbox->contact;
        return $contact?->phone_number;
    }
}

