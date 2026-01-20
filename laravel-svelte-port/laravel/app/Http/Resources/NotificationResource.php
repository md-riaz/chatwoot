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
        return [
            'id' => $this->id,
            'accountId' => $this->account_id,
            'userId' => $this->user_id,
            'notificationType' => $this->notification_type_string ?? 'unknown',
            'primaryActorType' => $this->primary_actor_type,
            'primaryActorId' => $this->primary_actor_id,
            'primaryActor' => $this->mapPrimaryActor($this->primaryActor),
            'readAt' => $this->read_at,
            'snoozedUntil' => $this->snoozed_until,
            'createdAt' => $this->created_at->timestamp,
            'lastActivityAt' => $this->last_activity_at ? $this->last_activity_at->timestamp : $this->created_at->timestamp,
            'meta' => $this->meta,
            'pushMessageTitle' => $this->push_message_title,
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
