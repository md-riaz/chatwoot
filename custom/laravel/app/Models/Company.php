<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_id',
        'name',
        'domain',
        'description',
        'avatar_url',
    ];

    protected $casts = [
        'contacts_count' => 'integer',
    ];

    // Validation rules
    public static function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'domain' => [
                'nullable',
                'string',
                'regex:/^[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?)+$/',
            ],
            'description' => 'nullable|string|max:1000',
            'avatar_url' => 'nullable|url',
        ];
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    // Scopes
    public function scopeOrderedByName(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    public function scopeSearchByNameOrDomain(Builder $query, string $searchTerm): Builder
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('name', 'ilike', "%{$searchTerm}%")
              ->orWhere('domain', 'ilike', "%{$searchTerm}%");
        });
    }

    public function scopeOrderedByDomain(Builder $query): Builder
    {
        return $query->orderBy('domain');
    }

    public function scopeOrderedByCreatedAt(Builder $query): Builder
    {
        return $query->orderBy('created_at');
    }

    public function updateContactsCount(): void
    {
        $this->updateQuietly(['contacts_count' => $this->contacts()->count()]);
    }
}
