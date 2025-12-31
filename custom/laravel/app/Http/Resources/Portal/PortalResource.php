<?php

namespace App\Http\Resources\Portal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'custom_domain' => $this->custom_domain,
            'color' => $this->color,
            'homepage_link' => $this->homepage_link,
            'page_title' => $this->page_title,
            'header_text' => $this->header_text,
            'archived' => $this->archived,
            'config' => $this->config,
            'ssl_settings' => $this->ssl_settings,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
