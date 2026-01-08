<?php

namespace App\Http\Resources\Article;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'portal_id' => $this->portal_id,
            'category_id' => $this->category_id,
            'folder_id' => $this->folder_id,
            'author_id' => $this->author_id,
            'associated_article_id' => $this->associated_article_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'content' => $this->content,
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'position' => $this->position,
            'views' => $this->views,
            'locale' => $this->locale,
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    private function statusLabel(): string
    {
        return match ((int) $this->status) {
            \App\Models\Article::STATUS_PUBLISHED => 'published',
            \App\Models\Article::STATUS_ARCHIVED => 'archived',
            default => 'draft',
        };
    }
}
