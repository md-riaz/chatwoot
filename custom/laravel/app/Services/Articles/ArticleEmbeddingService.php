<?php

namespace App\Services\Articles;

use App\Models\Article;
use App\Repositories\ArticleEmbeddingRepo;
use App\Services\Integrations\OpenAIService;
use Illuminate\Support\Facades\Log;

class ArticleEmbeddingService
{
    public function __construct(private ArticleEmbeddingRepo $repo, private OpenAIService $openai)
    {
    }

    /**
     * Generate embedding for an article and persist it.
     */
    public function generateAndStore(Article $article, string $model = 'text-embedding-ada-002'): ?array
    {
        try {
            $text = trim($article->content ?? ($article->title ?? ''));
            if ($text === '') {
                return null;
            }

            $resp = $this->openai->embeddings($text, $model);

            if (! ($resp['success'] ?? false)) {
                Log::warning('ArticleEmbeddingService: embeddings failed', ['article_id' => $article->id, 'error' => $resp['error'] ?? null]);
                return null;
            }

            $embedding = $resp['embedding'] ?? null;

            if (! $embedding) {
                return null;
            }

            $record = $this->repo->updateOrCreate([
                'article_id' => $article->id,
            ], [
                'account_id' => $article->account_id,
                'embedding' => $embedding,
                'model' => $model,
            ]);

            return $record->toArray();
        } catch (\Throwable $e) {
            Log::error('ArticleEmbeddingService::generateAndStore error', ['error' => $e->getMessage(), 'article_id' => $article->id]);
            return null;
        }
    }
}
