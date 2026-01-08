<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tagging extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'tag_id',
        'taggable_type',
        'taggable_id',
        'tagger_type',
        'tagger_id',
        'context',
        'created_at',
    ];

    protected $casts = [
        'tag_id' => 'integer',
        'taggable_id' => 'integer',
        'tagger_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
