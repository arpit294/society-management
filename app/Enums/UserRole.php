<?php

namespace App\Enums;

/**
 * UserRole Enum
 *
 * Defines the application's supported roles as a single source of truth.
 * This prevents hardcoded string typos when checking or validating roles.
 */
enum UserRole: string
{
    case OWNER = 'owner';
    case RENTAL = 'rental';
    case SECURITY = 'security';
    case COMMITTEE_MEMBER = 'committee_member';
    case SECRETARY = 'secretary';

    /**
     * Get a list of all raw values.
     * Useful for validation rules or dropdown configurations.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
