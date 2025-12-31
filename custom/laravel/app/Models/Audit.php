<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * Get the user that performed the audit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model.
     */
    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * Get the associated model.
     */
    public function associated()
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter by event type.
     */
    public function scopeEvent($query, $event)
    {
        return $query->where('action', $event);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Get old values (alias for audited_changes).
     */
    public function getOldValuesAttribute()
    {
        return $this->audited_changes;
    }

    /**
     * Get new values (alias for audited_changes).
     */
    public function getNewValuesAttribute()
    {
        return $this->audited_changes;
    }

    /**
     * Get IP address (alias for remote_address).
     */
    public function getIpAddressAttribute()
    {
        return $this->remote_address;
    }

    /**
     * Get event (alias for action).
     */
    public function getEventAttribute()
    {
        return $this->action;
    }
}
