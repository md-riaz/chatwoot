<?php

namespace App\Enums;

enum AccountUserRole: int
{
    case AGENT = 0;
    case ADMINISTRATOR = 1;

    /**
     * Get the role name as a string
     */
    public function getName(): string
    {
        return match($this) {
            self::AGENT => 'agent',
            self::ADMINISTRATOR => 'administrator',
        };
    }

    /**
     * Get role from string name
     */
    public static function fromName(string $name): self
    {
        return match(strtolower($name)) {
            'agent' => self::AGENT,
            'administrator', 'admin' => self::ADMINISTRATOR,
            default => throw new \InvalidArgumentException("Invalid role name: $name"),
        };
    }

    /**
     * Get all role names
     */
    public static function getNames(): array
    {
        return array_map(fn($role) => $role->getName(), self::cases());
    }

    /**
     * Get all role values
     */
    public static function getValues(): array
    {
        return array_map(fn($role) => $role->value, self::cases());
    }

    /**
     * Check if role is administrator
     */
    public function isAdministrator(): bool
    {
        return $this === self::ADMINISTRATOR;
    }

    /**
     * Check if role is agent
     */
    public function isAgent(): bool
    {
        return $this === self::AGENT;
    }
}