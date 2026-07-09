<?php

namespace App\Enums;

enum OrganizationStatus: string
{
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case OFF = 'off';

    // Optional helper - get all values as array
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    // Optional helper - human readable label
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::VERIFIED => 'Verified',
            self::OFF => 'Disabled',
        };
    }
}