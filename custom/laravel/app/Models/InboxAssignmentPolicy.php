<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboxAssignmentPolicy extends Model
{
    protected $fillable = [
        'inbox_id',
        'assignment_policy_id',
    ];

    public function inbox(): BelongsTo
    {
        return $this->belongsTo(Inbox::class);
    }

    public function assignmentPolicy(): BelongsTo
    {
        return $this->belongsTo(AssignmentPolicy::class);
    }
}