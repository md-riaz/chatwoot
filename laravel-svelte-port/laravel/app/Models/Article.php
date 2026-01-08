<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory;

    // Status constants
    public const STATUS_DRAFT = 0;

    public const STATUS_PUBLISHED = 1;

    public const STATUS_ARCHIVED = 2;

    protected $fillable = [
        'account_id',
        'portal_id',
        'category_id',
        'folder_id',
        'author_id',
        'associated_article_id',
        'title',
        'slug',
        'content',
        'description',
        'status',
        'position',
        'views',
        'locale',
        'meta',
    ];

    protected $casts = [
        'status' => 'integer',
        'position' => 'integer',
        'views' => 'integer',
        'meta' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Article $article) {
            if (! $article->account_id && $article->portal) {
                $article->account_id = $article->portal->account_id;
            }
            if (! $article->slug && $article->title) {
                $article->slug = time().'-'.str($article->title)->slug();
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function portal(): BelongsTo
    {
        return $this->belongsTo(Portal::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function rootArticle(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'associated_article_id');
    }

    public function associatedArticles(): HasMany
    {
        return $this->hasMany(Article::class, 'associated_article_id');
    }

    /**
     * Increment view count.
     */
    public function incrementViewCount(): void
    {
        $this->increment('views');
    }

    /**
     * Scope for draft articles.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope for published articles.
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope for archived articles.
     */
    public function scopeArchived($query)
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    /**
     * Scope to filter by locale.
     */
    public function scopeSearchByLocale($query, ?string $locale)
    {
        if ($locale) {
            return $query->where('locale', $locale);
        }

        return $query;
    }

    /**
     * Scope to filter by author.
     */
    public function scopeSearchByAuthor($query, ?int $authorId)
    {
        if ($authorId) {
            return $query->where('author_id', $authorId);
        }

        return $query;
    }
}
