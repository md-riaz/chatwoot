<?php

namespace App\Models\Channels;

use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class FacebookPage extends Model
{
    use HasFactory;

    protected $table = 'channel_facebook_pages';

    protected $fillable = [
        'account_id',
        'page_id',
        'user_access_token',
        'page_access_token',
        'instagram_id',
    ];

    protected $hidden = [
        'user_access_token',
        'page_access_token',
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
        return 'Facebook';
    }

    public function hasInstagram(): bool
    {
        return !empty($this->instagram_id);
    }
}
