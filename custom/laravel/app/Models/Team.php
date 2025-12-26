<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'name',
        'description',
        'allow_auto_assign',
    ];

    protected $casts = [
        'allow_auto_assign' => 'boolean',
    ];

    /**
     * Get the account that owns the team.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get all members (users) in the team.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_members')
            ->withTimestamps();
    }

    /**
     * Get all conversations assigned to the team.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
