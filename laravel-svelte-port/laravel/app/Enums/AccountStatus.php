<?php

namespace App\Enums;

enum AccountStatus: int
{
    case ACTIVE = 0;
    case SUSPENDED = 1;

    /**
     * Get the string representation of the status
     */
    public function getName(): string
    {
        return match($this) {
            self::ACTIVE => 'active',
            self::SUSPENDED => 'suspended',
        };
    }

    /**
     * Get the display name for the status
     */
    public function getDisplayName(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
        };
    }

    /**
     * Create enum from string value
     */
    public static function fromString(string $value): self
    {
        return match(strtolower($value)) {
            'active' => self::ACTIVE,
            'suspended' => self::SUSPENDED,
            default => throw new \ValueError("Invalid status value: {$value}"),
        };
    }

    /**
     * Get all available statuses as array
     */
    public static function toArray(): array
    {
        return [
            'active' => self::ACTIVE->value,
            'suspended' => self::SUSPENDED->value,
        ];
    }

    /**
     * Get all available statuses with display names
     */
    public static function options(): array
    {
        return [
            'active' => self::ACTIVE->getDisplayName(),
            'suspended' => self::SUSPENDED->getDisplayName(),
        ];
    }
}