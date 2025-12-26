<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'portal_id',
        'parent_category_id',
        'associated_category_id',
        'name',
        'slug',
        'description',
        'icon',
        'position',
        'locale',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (!$category->account_id && $category->portal) {
                $category->account_id = $category->portal->account_id;
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

    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_category_id');
    }

    public function rootCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'associated_category_id');
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_category_id');
    }

    public function associatedCategories(): HasMany
    {
        return $this->hasMany(Category::class, 'associated_category_id');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class);
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
}
