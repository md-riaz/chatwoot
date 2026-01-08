<?php

namespace App\Jobs\Articles;

use App\Models\Article;
use App\Services\Articles\ArticleEmbeddingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateArticleEmbeddingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $articleId;

    public function __construct(int $articleId)
    {
        $this->articleId = $articleId;
    }

    public function handle(ArticleEmbeddingService $service): void
    {
        $article = Article::find($this->articleId);
        if (! $article) {
            return;
        }

        $service->generateAndStore($article);
    }
}
