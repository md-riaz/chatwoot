<?php

namespace App\Http\Resources\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'overview' => [
                'accounts_count' => $this->accounts_count,
                'users_count' => $this->users_count,
                'conversations_count' => $this->conversations_count,
                'messages_count' => $this->messages_count,
                'contacts_count' => $this->contacts_count,
                'inboxes_count' => $this->inboxes_count,
                'agent_bots_count' => $this->agent_bots_count,
            ],
            'activity' => [
                'active_accounts' => $this->active_accounts,
                'recent_signups' => $this->recent_signups,
            ],
            'breakdown' => [
                'account_status' => $this->account_status,
                'user_roles' => $this->user_roles,
            ],
            'growth' => $this->growth,
            'system_health' => $this->system_health,
            'recent_activity' => $this->recent_activity,
        ];
    }
}