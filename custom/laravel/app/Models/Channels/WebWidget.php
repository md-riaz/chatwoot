<?php

namespace App\Models\Channels;

use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class WebWidget extends Model
{
    use HasFactory;

    protected $table = 'channel_web_widgets';

    protected $fillable = [
        'website_url',
        'website_token',
        'widget_color',
        'welcome_title',
        'welcome_tagline',
        'feature_flags',
        'pre_chat_form_options',
        'pre_chat_form_enabled',
    ];

    protected $casts = [
        'feature_flags' => 'boolean',
        'pre_chat_form_options' => 'array',
        'pre_chat_form_enabled' => 'boolean',
    ];

    /**
     * Get the inbox for this channel.
     */
    public function inbox(): MorphOne
    {
        return $this->morphOne(Inbox::class, 'channel');
    }
}
