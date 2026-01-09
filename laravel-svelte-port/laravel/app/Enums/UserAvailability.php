<?php

namespace App\Enums;

enum UserAvailability: int
{
    case ONLINE = 0;   // Rails: online: 0
    case OFFLINE = 1;  // Rails: offline: 1  
    case BUSY = 2;     // Rails: busy: 2

    /**
     * Get the availability name as a string
     */
    public function getName(): string
    {
        return match($this) {
            self::ONLINE => 'online',
            self::OFFLINE => 'offline',
            self::BUSY => 'busy',
        };
    }

    /**
     * Get availability from string name
     */
    public static function fromName(string $name): self
    {
        return match(strtolower($name)) {
            'online' => self::ONLINE,
            'offline' => self::OFFLINE,
            'busy' => self::BUSY,
            default => throw new \InvalidArgumentException("Invalid availability: $name"),
        };
    }

    /**
     * Get all availability names
     */
    public static function getNames(): array
    {
        return array_map(fn($availability) => $availability->getName(), self::cases());
    }

    /**
     * Check if user is available
     */
    public function isAvailable(): bool
    {
        return $this === self::ONLINE;
    }
}