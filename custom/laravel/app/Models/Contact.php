<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_id',
        'company_id',
        'name',
        'email',
        'phone_number',
        'identifier',
        'avatar_url',
        'custom_attributes',
        'additional_attributes',
        'last_activity_at',
        'blocked',
        'country_code',
        'city',
    ];

    protected $casts = [
        'custom_attributes' => 'array',
        'additional_attributes' => 'array',
        'last_activity_at' => 'datetime',
        'blocked' => 'boolean',
    ];

    /**
     * Get the account that owns the contact.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the company that owns the contact.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get all conversations for the contact.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Get all contact inboxes for the contact.
     */
    public function contactInboxes(): HasMany
    {
        return $this->hasMany(ContactInbox::class);
    }

    /**
     * Get all labels for the contact.
     */
    public function labels(): MorphToMany
    {
        return $this->morphToMany(Label::class, 'labelable', 'labelings');
    }

    /**
     * Get all notes for the contact.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Get all media files for the contact (avatar, etc).
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}
