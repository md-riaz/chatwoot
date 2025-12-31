<?php

namespace App\Events\Article;

use App\Http\Resources\Article\ArticleResource;
use App\Models\Article;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ArticleUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Article $article,
        public string $action = 'updated',
        public ?int $previousStatus = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("account.{$this->article->account_id}"),
            new PrivateChannel("portal.{$this->article->portal_id}"),
            new PrivateChannel("article.{$this->article->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'article.' . $this->action;
    }

    public function broadcastWith(): array
    {
        return [
            'article' => new ArticleResource($this->article),
            'action' => $this->action,
            'previous_status' => $this->previousStatus,
        ];
    }
}
