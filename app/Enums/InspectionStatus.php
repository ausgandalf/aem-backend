<?php

// app/Enums/OrganisationStatus.php

namespace App\Enums;

enum InspectionStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
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
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }
}