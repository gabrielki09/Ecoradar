import type { UserResource } from './types/user.types';

/** A conclusão vem de /auth/me, persistida em onboarding_completed_at no servidor. */
export function canRequestCollections(user: UserResource | null): boolean {
    return user?.profile_type === 'common' && user.onboarding_completed;
}

export function canProvideCollections(user: UserResource | null): boolean {
    return (user?.profile_type === 'collector' || user?.profile_type === 'company') && user.onboarding_completed;
}

export function pendingOnboardingPath(user: UserResource | null): string | null {
    if (!user || user.onboarding_completed) return null;
    if (user.profile_type === 'collector') return '/onboarding/collector';
    if (user.profile_type === 'company') return '/onboarding/company';
    return null;
}
