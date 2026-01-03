<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignmentPolicy extends Model
{
    protected $fillable = [
        'name',
        'description',
        'enabled',
        'assignment_order',
        'conversation_priority',
        'fair_distribution_limit',
        'fair_distribution_window',
        'account_id',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'fair_distribution_limit' => 'integer',
        'fair_distribution_window' => 'integer',
    ];

    // Enums
    public const ASSIGNMENT_ORDER_ROUND_ROBIN = 'round_robin';
    public const CONVERSATION_PRIORITY_EARLIEST_CREATED = 'earliest_created';
    public const CONVERSATION_PRIORITY_LONGEST_WAITING = 'longest_waiting';

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function inboxAssignmentPolicies(): HasMany
    {
        return $this->hasMany(InboxAssignmentPolicy::class);
    }

    public function inboxes()
    {
        return $this->belongsToMany(Inbox::class, 'inbox_assignment_policies');
    }
}