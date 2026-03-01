<?php

namespace App\Http\Resources\Conversation;

use App\Http\Resources\Contact\ContactResource;
use App\Http\Resources\Inbox\InboxResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'inbox_id' => $this->inbox_id,
            'contact_id' => $this->contact_id,
            'assignee_id' => $this->assignee_id,
            'team_id' => $this->team_id,
            'display_id' => $this->display_id,
            'status' => $this->status,
            'priority' => $this->priority,
            'muted' => (bool) $this->muted,
            'uuid' => $this->uuid,
            'custom_attributes' => $this->custom_attributes,
            'first_reply_created_at' => $this->first_reply_created_at?->toISOString(),
            'last_activity_at' => $this->last_activity_at?->toISOString(),
            'waiting_since' => $this->waiting_since?->toISOString(),
            'snoozed_until' => $this->snoozed_until?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Rails parity: meta object with sender, channel, assignee, team
            'meta' => $this->buildMeta(),

            // Relationships (when loaded) — kept for backward compat
            'contact' => new ContactResource($this->whenLoaded('contact')),
            'inbox' => new InboxResource($this->whenLoaded('inbox')),
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'messages_count' => $this->whenCounted('messages'),
        ];
    }

    /**
     * Build the meta object matching Rails API format.
     * Rails: meta.sender, meta.channel, meta.assignee, meta.team, meta.hmac_verified
     */
    private function buildMeta(): array
    {
        $meta = [];

        // sender = contact (Rails wraps contact as meta.sender)
        if ($this->relationLoaded('contact') && $this->contact) {
            $meta['sender'] = new ContactResource($this->contact);
        }

        // channel = inbox channel_type
        if ($this->relationLoaded('inbox') && $this->inbox) {
            $meta['channel'] = $this->inbox->channel_type;
        }

        // assignee
        if ($this->relationLoaded('assignee') && $this->assignee) {
            $meta['assignee'] = new UserResource($this->assignee);
        }

        // team
        if ($this->relationLoaded('team') && $this->team) {
            $meta['team'] = [
                'id' => $this->team->id,
                'name' => $this->team->name,
            ];
        }

        $meta['hmac_verified'] = true; // Default for non-widget inboxes

        return $meta;
    }
}
