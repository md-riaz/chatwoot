<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActiveStorageAttachment extends Model
{
    use HasFactory;

    protected $table = 'active_storage_attachments';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'record_type',
        'record_id',
        'blob_id',
        'created_at',
    ];

    protected $casts = [
        'record_id' => 'integer',
        'blob_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function blob(): BelongsTo
    {
        return $this->belongsTo(ActiveStorageBlob::class, 'blob_id');
    }
}
