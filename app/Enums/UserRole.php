<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Birthday = 'birthday';
    case Disabled = 'disabled';

    /**
     * Get all available roles (excluding disabled)
     */
    public static function options(): array
    {
        return [
            self::Admin->value => 'Admin',
            self::Birthday->value => 'Birthday Secretary',
        ];
    }

    /**
     * Get the display name for the role
     */
    public function label(): string
    {
        return match($this) {
            self::Admin => 'Admin',
            self::Birthday => 'Birthday Secretary',
            self::Disabled => 'Disabled',
        };
    }
}
