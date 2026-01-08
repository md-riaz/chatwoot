<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_type',
        'subscription_attributes',
        'identifier',
    ];

    protected $casts = [
        'subscription_type' => 'string',
        'subscription_attributes' => 'array',
    ];
}
