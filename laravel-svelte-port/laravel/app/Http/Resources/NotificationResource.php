<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Rails parity: direct column access
        $notificationType = $this->notification_type;
        
        // Map integer type to string
        $types = array_flip(\App\Models\NotificationSetting::NOTIFICATION_TYPES);
        $typeString = $types[$notificationType] ?? 'unknown';

        return [
            'id' => $this->id,
            'accountId' => $this->account_id,
            'userId' => $this->user_id,
            'notificationType' => $typeString,
            'primaryActorType' => $this->primary_actor_type,
            'primaryActorId' => $this->primary_actor_id,
            'primaryActor' => $this->mapPrimaryActor($this->primaryActor),
            'readAt' => $this->read_at,
            'snoozedUntil' => $this->snoozed_until,
            'createdAt' => $this->created_at->timestamp,
            'lastActivityAt' => $this->last_activity_at ? $this->last_activity_at->timestamp : $this->created_at->timestamp,
            'meta' => $this->meta,
            'pushMessageTitle' => $this->meta['push_message_title'] ?? null,
        ];
    }

    private function mapPrimaryActor($actor)
    {
        if (!$actor) {
            return null;
        }

        if ($actor instanceof \App\Models\Conversation) {
            $contact = $actor->contact;
            return [
                'id' => $actor->id,
                'name' => $contact ? $contact->name : 'Conversation #' . $actor->display_id,
                'thumbnail' => $contact ? $contact->avatar_url : null,
            ];
        }

        if ($actor instanceof \App\Models\User) {
            return [
                'id' => $actor->id,
                'name' => $actor->name,
                'thumbnail' => $actor->avatar_url,
            ];
        }

        if ($actor instanceof \App\Models\Contact) {
            return [
                'id' => $actor->id,
                'name' => $actor->name,
                'thumbnail' => $actor->avatar_url,
            ];
        }

        return [
            'id' => $actor->id,
            'name' => class_basename($actor) . ' #' . $actor->id,
            'thumbnail' => null,
        ];
    }
}
