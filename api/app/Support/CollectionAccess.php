<?php

namespace App\Support;

use App\Enum\ProfileTypes;
use App\Models\User;

final class CollectionAccess
{
    public static function requester(User $user): bool
    {
        return $user->profile_type === ProfileTypes::COMMON && $user->onboarding_completed_at !== null;
    }

    public static function provider(User $user): bool
    {
        return in_array($user->profile_type, [ProfileTypes::COLLECTOR, ProfileTypes::COMPANY], true)
            && $user->onboarding_completed_at !== null;
    }
}
