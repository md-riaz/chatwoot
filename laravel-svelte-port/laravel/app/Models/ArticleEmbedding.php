<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleEmbedding extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'article_id',
        'embedding',
        'model',
        'similarity',
    ];

    protected $casts = [
        'embedding' => 'array',
        'similarity' => 'float',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
