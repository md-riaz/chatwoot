<?php

namespace App\Repositories;

use App\Models\ArticleEmbedding;

class ArticleEmbeddingRepo
{
    public function updateOrCreate(array $attributes, array $values): ArticleEmbedding
    {
        return ArticleEmbedding::updateOrCreate($attributes, $values);
    }

    public function create(array $data): ArticleEmbedding
    {
        return ArticleEmbedding::create($data);
    }

    public function findByArticle(int $articleId)
    {
        return ArticleEmbedding::where('article_id', $articleId)->get();
    }
}
