<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'user_id',
        'role',
        'custom_role_id',
        'active_at',
        'availability',
        'settings',
    ];

    protected $casts = [
        'active_at' => 'boolean',
        'availability' => 'integer',
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
     * Get the custom role for the account user.
     */
    public function customRole(): BelongsTo
    {
        return $this->belongsTo(CustomRole::class);
    }

    /**
     * Get the role name.
     */
    public function getRoleNameAttribute(): string
    {
        return match ($this->role) {
            1 => 'agent',
            2 => 'admin',
            default => 'unknown',
        };
    }

    /**
     * Get the availability name.
     */
    public function getAvailabilityNameAttribute(): string
    {
        return match ($this->availability) {
            0 => 'offline',
            1 => 'online',
            default => 'unknown',
        };
    }

    /**
     * Scope to filter by role.
     */
    public function scopeRole($query, $role)
    {
        if (is_string($role)) {
            $roleMap = ['agent' => 1, 'admin' => 2];
            $role = $roleMap[$role] ?? $role;
        }

        return $query->where('role', $role);
    }

    /**
     * Scope to filter by availability.
     */
    public function scopeAvailable($query)
    {
        return $query->where('availability', 1);
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
        // Admin role has all permissions
        if ($this->role === 2) { // admin
            return true;
        }

        // Agent role has limited permissions
        if ($this->role === 1) { // agent
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
        if ($this->role === 2) { // admin
            return CustomRole::PERMISSIONS;
        }

        if ($this->role === 1) { // agent
            return [
                'conversation_participating_manage',
                'contact_manage',
            ];
        }

        return [];
    }
}