<?php

namespace App\Observers;

use App\Jobs\Articles\GenerateArticleEmbeddingJob;
use App\Models\Article;

class ArticleObserver
{
    public function created(Article $article)
    {
        // Dispatch embedding generation asynchronously
        GenerateArticleEmbeddingJob::dispatch($article->id);
    }

    public function updated(Article $article)
    {
        // Re-generate embedding on update (idempotent inside job)
        GenerateArticleEmbeddingJob::dispatch($article->id);
    }
}
