<?php

namespace App\Http\Resources\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditResource extends JsonResource
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
            'auditable_id' => $this->auditable_id,
            'auditable_type' => $this->auditable_type,
            'model_name' => $this->model_name,
            'user_id' => $this->user_id,
            'username' => $this->username,
            'action' => $this->action,
            'event_name' => $this->event_name,
            'audited_changes' => $this->audited_changes,
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
            'version' => $this->version,
            'comment' => $this->comment,
            'remote_address' => $this->remote_address,
            'ip_address' => $this->ip_address,
            'request_uuid' => $this->request_uuid,
            'created_at' => $this->created_at,
            
            // Relationships (when loaded)
            'user' => new UserResource($this->whenLoaded('user')),
            'related_audits' => AuditResource::collection($this->whenLoaded('relatedAudits')),
        ];
    }
}