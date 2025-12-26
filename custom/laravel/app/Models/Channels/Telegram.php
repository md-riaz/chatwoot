<?php

namespace App\Models\Channels;

use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Telegram extends Model
{
    use HasFactory;

    protected $table = 'channel_telegram';

    protected $fillable = [
        'account_id',
        'bot_token',
        'bot_name',
    ];

    protected $hidden = [
        'bot_token',
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
        return 'Telegram';
    }

    public function getTelegramApiUrl(): string
    {
        return "https://api.telegram.org/bot{$this->bot_token}";
    }
}
