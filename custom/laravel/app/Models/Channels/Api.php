<?php

namespace App\Models\Channels;

use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Api extends Model
{
    use HasFactory;

    protected $table = 'channel_apis';

    protected $fillable = [
        'webhook_url',
        'hmac_token',
        'hmac_mandatory',
        'additional_attributes',
    ];

    protected $hidden = [
        'hmac_token',
    ];

    protected $casts = [
        'hmac_mandatory' => 'boolean',
        'additional_attributes' => 'array',
    ];

    /**
     * Get the inbox for this channel.
     */
    public function inbox(): MorphOne
    {
        return $this->morphOne(Inbox::class, 'channel');
    }
}
