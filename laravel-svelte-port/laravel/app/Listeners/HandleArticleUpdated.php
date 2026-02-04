<?php

namespace App\Listeners;

use App\Events\Article\ArticleUpdated;
use App\Jobs\Webhooks\SendWebhooksJob;
use Psr\Log\LoggerInterface;
use function Spatie\Activitylog\activity;

class HandleArticleUpdated
{
    public function __construct(private LoggerInterface $log) {}

    public function handle(ArticleUpdated $event): void
    {
        $article = $event->article;
        $eventName = $this->resolveWebhookEvent($event->action);

        SendWebhooksJob::dispatch($article->account_id, $eventName, [
            'article_id' => $article->id,
            'portal_id' => $article->portal_id,
            'action' => $event->action,
            'previous_status' => $event->previousStatus,
        ]);

        activity()
            ->performedOn($article)
            ->withProperties([
                'action' => $event->action,
                'portal_id' => $article->portal_id,
                'previous_status' => $event->previousStatus,
            ])
            ->event($eventName)
            ->log('Article lifecycle change');

        $this->log->info('Article lifecycle event dispatched', [
            'article_id' => $article->id,
            'action' => $event->action,
        ]);
    }

    private function resolveWebhookEvent(string $action): string
    {
        return match ($action) {
            'created' => 'article_created',
            'deleted' => 'article_deleted',
            'published' => 'article_published',
            'archived' => 'article_archived',
            default => 'article_updated',
        };
    }
}
