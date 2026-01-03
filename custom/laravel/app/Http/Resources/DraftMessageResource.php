<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DraftMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (! $this->resource) {
            return ['has_draft' => false];
        }

        return [
            'has_draft' => true,
            'message' => $this->resource['message'],
            'updated_at' => $this->resource['updated_at'],
            'user_id' => $this->resource['user_id'],
        ];
    }
}