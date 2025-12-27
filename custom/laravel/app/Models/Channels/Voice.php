<?php

namespace App\Models\Channels;

use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Voice extends Model
{
    use HasFactory;

    protected $table = 'channel_voice';

    protected $fillable = [
        'account_id',
        'phone_number',
        'provider',
        'provider_config',
        'additional_attributes',
    ];

    protected $casts = [
        'provider_config' => 'array',
        'additional_attributes' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function inbox(): HasOne
    {
        return $this->hasOne(Inbox::class, 'channel_id')->where('channel_type', self::class);
    }

    public function name(): string
    {
        return 'Voice';
    }
}
