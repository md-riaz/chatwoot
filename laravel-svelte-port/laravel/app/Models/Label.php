<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Label extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'title',
        'description',
        'color',
        'show_on_sidebar',
    ];

    protected $casts = [
        'show_on_sidebar' => 'boolean',
    ];

    /**
     * Get the account that owns the label.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get all conversations with this label.
     */
    public function conversations(): MorphToMany
    {
        return $this->morphedByMany(Conversation::class, 'labelable', 'labelings');
    }

    /**
     * Get all contacts with this label.
     */
    public function contacts(): MorphToMany
    {
        return $this->morphedByMany(Contact::class, 'labelable', 'labelings');
    }
}
