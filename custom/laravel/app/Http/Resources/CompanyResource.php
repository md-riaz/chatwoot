<?php

namespace App\Http\Resources;

use App\Http\Resources\Contact\ContactResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'name' => $this->name,
            'domain' => $this->domain,
            'description' => $this->description,
            'contacts_count' => $this->contacts_count,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'contacts' => $this->whenLoaded('contacts', function () {
                return ContactResource::collection($this->contacts);
            }),
        ];
    }
}
