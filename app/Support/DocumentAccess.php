<?php

namespace App\Support;

use App\Models\User;

class DocumentAccess
{
    /**
     * The stage keys whose documents this user may see.
     *
     * Returns null when the user may see documents on ANY stage ('*'),
     * or an array of stage keys otherwise (empty array = none).
     */
    public static function visibleStages(User $user): ?array
    {
        $map = config('workflow.document_visibility', []);

        // Union across all of the user's roles (a user may hold more than one)
        $stages = [];
        foreach ($user->getRoleNames() as $role) {
            $allowed = $map[$role] ?? [];

            if (in_array('*', $allowed, true)) {
                return null; // any stage
            }

            $stages = array_merge($stages, $allowed);
        }

        return array_values(array_unique($stages));
    }

    /** Whether the user can see documents submitted on the given stage. */
    public static function canSeeStage(User $user, ?string $stageKey): bool
    {
        $stages = self::visibleStages($user);

        return $stages === null || in_array($stageKey, $stages, true);
    }
}
