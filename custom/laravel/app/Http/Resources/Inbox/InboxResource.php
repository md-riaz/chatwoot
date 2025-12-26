<?php

namespace App\Http\Resources\Inbox;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InboxResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'name' => $this->name,
            'channel_type' => $this->channel_type,
            'channel_id' => $this->channel_id,
            'enable_auto_assignment' => $this->enable_auto_assignment,
            'greeting_enabled' => $this->greeting_enabled,
            'greeting_message' => $this->greeting_message,
            'enable_email_collect' => $this->enable_email_collect,
            'csat_survey_enabled' => $this->csat_survey_enabled,
            'allow_messages_after_resolved' => $this->allow_messages_after_resolved,
            'working_hours' => $this->working_hours,
            'timezone' => $this->timezone,
            'working_hours_enabled' => $this->working_hours_enabled,
            'out_of_office_message' => $this->out_of_office_message,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships (when loaded)
            'channel' => $this->whenLoaded('channel'),
            'members_count' => $this->whenCounted('members'),
        ];
    }
}
