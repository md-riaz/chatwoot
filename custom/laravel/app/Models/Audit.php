<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'auditable_id',
        'auditable_type',
        'associated_id',
        'associated_type',
        'user_id',
        'user_type',
        'username',
        'action',
        'audited_changes',
        'version',
        'comment',
        'remote_address',
        'request_uuid',
        'created_at',
    ];

    protected $casts = [
        'audited_changes' => 'array',
        'version' => 'integer',
        'created_at' => 'datetime',
    ];
}
