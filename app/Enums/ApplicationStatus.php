<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case PENDING = 'pending';
    case INPROGRESS = 'in_progress';
    case ONHOLD = 'on_hold';
    case PASSED = 'passed';
    case REJECTED = 'rejected';

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
            self::INPROGRESS => 'In Progress',
            self::ONHOLD => 'On Hold',
            self::PASSED => 'Passed',
            self::REJECTED => 'Rejected',
        };
    }
}