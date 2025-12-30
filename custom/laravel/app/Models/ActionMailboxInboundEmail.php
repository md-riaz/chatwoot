<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionMailboxInboundEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'message_id',
        'message_checksum',
    ];
}
