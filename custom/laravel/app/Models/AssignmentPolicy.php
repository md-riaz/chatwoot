<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignmentPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'name',
        'description',
        'assignment_order',
        'conversation_priority',
        'fair_distribution_limit',
        'fair_distribution_window',
        'enabled',
    ];

    protected $casts = [
        'assignment_order' => 'integer',
        'conversation_priority' => 'integer',
        'fair_distribution_limit' => 'integer',
        'fair_distribution_window' => 'integer',
        'enabled' => 'boolean',
    ];

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
