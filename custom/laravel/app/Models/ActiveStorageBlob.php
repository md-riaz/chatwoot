<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActiveStorageBlob extends Model
{
    use HasFactory;

    protected $table = 'active_storage_blobs';

    public $timestamps = false;

    protected $fillable = [
        'key',
        'filename',
        'content_type',
        'metadata',
        'byte_size',
        'checksum',
        'service_name',
        'created_at',
    ];

    protected $casts = [
        'byte_size' => 'integer',
        'created_at' => 'datetime',
    ];
}
