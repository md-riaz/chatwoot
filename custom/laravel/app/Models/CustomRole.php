<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomRole extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'custom_roles';

    // Available permissions for custom roles
    public const PERMISSIONS = [
        'conversation_manage',
        'conversation_unassigned_manage',
        'conversation_participating_manage',
        'contact_manage',
        'report_manage',
        'knowledge_base_manage',
    ];

    protected $fillable = [
        'account_id',
        'name',
        'description',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function accountUsers(): HasMany
    {
        return $this->hasMany(AccountUser::class);
    }

    /**
     * Validate that all permissions are valid
     */
    public function validatePermissions(): bool
    {
        if (empty($this->permissions)) {
            return true;
        }

        foreach ($this->permissions as $permission) {
            if (!in_array($permission, self::PERMISSIONS)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the role has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Get all available permissions
     */
    public static function getAvailablePermissions(): array
    {
        return self::PERMISSIONS;
    }

    /**
     * Get permission descriptions
     */
    public static function getPermissionDescriptions(): array
    {
        return [
            'conversation_manage' => 'Can manage all conversations',
            'conversation_unassigned_manage' => 'Can manage unassigned conversations and assign to self',
            'conversation_participating_manage' => 'Can manage conversations they are participating in (assigned to or a participant)',
            'contact_manage' => 'Can manage contacts',
            'report_manage' => 'Can manage reports',
            'knowledge_base_manage' => 'Can manage knowledge base portals',
        ];
    }

    /**
     * Boot method to add model event listeners
     */
    protected static function booted(): void
    {
        static::saving(function (CustomRole $customRole) {
            if (!$customRole->validatePermissions()) {
                throw new \InvalidArgumentException('Invalid permissions provided');
            }
        });
    }
}
