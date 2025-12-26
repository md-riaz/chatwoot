<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactInbox extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'inbox_id',
        'source_id',
    ];

    /**
     * Get the contact that owns the contact inbox.
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the inbox that owns the contact inbox.
     */
    public function inbox(): BelongsTo
    {
        return $this->belongsTo(Inbox::class);
    }
}
