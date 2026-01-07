<?php

namespace App\Models;

use App\Enums\AccountUserRole;
use App\Enums\UserAvailability;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'user_id',
        'inviter_id',
        'role',
        'custom_role_id',
        'agent_capacity_policy_id',
        'active_at',
        'availability',
        'settings',
    ];

    protected $casts = [
        'role' => AccountUserRole::class,
        'availability' => UserAvailability::class,
        'active_at' => 'boolean',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the account that owns the account user.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the user that owns the account user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who invited this account user.
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    /**
     * Get the custom role for the account user.
     */
    public function customRole(): BelongsTo
    {
        return $this->belongsTo(CustomRole::class);
    }

    /**
     * Get the agent capacity policy for the account user.
     */
    public function agentCapacityPolicy(): BelongsTo
    {
        return $this->belongsTo(AgentCapacityPolicy::class);
    }

    /**
     * Get the role name.
     */
    public function getRoleNameAttribute(): string
    {
        return $this->role->getName();
    }

    /**
     * Get the availability name.
     */
    public function getAvailabilityNameAttribute(): string
    {
        return $this->availability->getName();
    }

    /**
     * Scope to filter by role.
     */
    public function scopeRole($query, AccountUserRole|string $role)
    {
        if (is_string($role)) {
            $role = AccountUserRole::fromName($role);
        }

        return $query->where('role', $role->value);
    }

    /**
     * Scope to filter by availability.
     */
    public function scopeAvailable($query)
    {
        return $query->where('availability', UserAvailability::ONLINE->value);
    }

    /**
     * Scope to filter by active status.
     */
    public function scopeActive($query)
    {
        return $query->where('active_at', true);
    }

    /**
     * Check if the account user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        // If user has a custom role, check custom role permissions
        if ($this->custom_role_id && $this->customRole) {
            return $this->customRole->hasPermission($permission);
        }

        // Default role-based permissions
        return $this->hasDefaultPermission($permission);
    }

    /**
     * Check default role-based permissions
     */
    private function hasDefaultPermission(string $permission): bool
    {
        // Administrator role has all permissions
        if ($this->role->isAdministrator()) {
            return true;
        }

        // Agent role has limited permissions
        if ($this->role->isAgent()) {
            $agentPermissions = [
                'conversation_participating_manage',
                'contact_manage',
            ];
            return in_array($permission, $agentPermissions);
        }

        return false;
    }

    /**
     * Get all permissions for this account user
     */
    public function getPermissions(): array
    {
        if ($this->custom_role_id && $this->customRole) {
            return $this->customRole->permissions ?? [];
        }

        // Return default permissions based on role
        if ($this->role->isAdministrator()) {
            return CustomRole::PERMISSIONS;
        }

        if ($this->role->isAgent()) {
            return [
                'conversation_participating_manage',
                'contact_manage',
            ];
        }

        return [];
    }
}