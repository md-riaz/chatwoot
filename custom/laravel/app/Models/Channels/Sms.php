<?php

namespace App\Models\Channels;

use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Sms extends Model
{
    use HasFactory;

    protected $table = 'channel_sms';

    // Provider constants
    public const PROVIDER_BANDWIDTH = 'bandwidth';

    protected $fillable = [
        'account_id',
        'phone_number',
        'provider',
        'provider_config',
    ];

    protected $casts = [
        'provider_config' => 'array',
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
        return 'Sms';
    }
}
