<?php

namespace App\Repositories;

use App\Models\ArticleEmbedding;

class ArticleEmbeddingRepository
{
    public function create(array $data): ArticleEmbedding
    {
        return ArticleEmbedding::create($data);
    }

    public function updateOrCreate(array $attributes, array $values): ArticleEmbedding
    {
        return ArticleEmbedding::updateOrCreate($attributes, $values);
    }

    public function forArticle(int $articleId)
    {
        return ArticleEmbedding::where('article_id', $articleId)->get();
    }

    public function deleteForArticle(int $articleId): int
    {
        return ArticleEmbedding::where('article_id', $articleId)->delete();
    }

    public function createForArticle(int $accountId, int $articleId, array $embedding, ?string $model = null, ?float $similarity = null): ArticleEmbedding
    {
        return ArticleEmbedding::create([
            'account_id' => $accountId,
            'article_id' => $articleId,
            'embedding' => $embedding,
            'model' => $model,
            'similarity' => $similarity,
        ]);
    }

    public function findByArticle(int $articleId)
    {
        return ArticleEmbedding::where('article_id', $articleId)->get();
    }
}
        
