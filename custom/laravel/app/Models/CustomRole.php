<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'account_id',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function accountUsers(): HasMany
    {
        return $this->hasMany(AccountUser::class);
    }
}
