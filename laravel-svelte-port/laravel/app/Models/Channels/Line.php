<?php

namespace App\Models\Channels;

use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Line extends Model
{
    use HasFactory;

    protected $table = 'channel_line';

    protected $fillable = [
        'account_id',
        'line_channel_id',
        'line_channel_secret',
        'line_channel_token',
    ];

    protected $hidden = [
        'line_channel_secret',
        'line_channel_token',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function inbox(): MorphOne
    {
        return $this->morphOne(Inbox::class, 'channel');
    }

    public function getName(): string
    {
        return 'Line';
    }

    /**
     * Get the Line API client for this channel.
     */
    public function getClient(): \App\Services\Channels\Line\LineApiClient
    {
        return new \App\Services\Channels\Line\LineApiClient($this);
    }
}
