<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CannedResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'short_code',
        'content',
    ];

    /**
     * Get the account that owns the canned response.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
