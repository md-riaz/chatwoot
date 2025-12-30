<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActiveStorageVariant extends Model
{
    use HasFactory;

    protected $table = 'active_storage_variant_records';

    public $timestamps = false;

    protected $fillable = [
        'blob_id',
        'variation_digest',
    ];

    protected $casts = [
        'blob_id' => 'integer',
    ];

    public function blob(): BelongsTo
    {
        return $this->belongsTo(ActiveStorageBlob::class, 'blob_id');
    }
}
