import type { Router } from 'vue-router';
import type { AuthResponse } from '../types/auth.types';

export default function getAuthDestination(router: Router, redirect: unknown, authentication: AuthResponse): string {
    if (authentication.onboarding.required) {
        return authentication.onboarding.next_step === 'company'
            ? '/onboarding/company'
            : '/onboarding/collector';
    }

    if (typeof redirect === 'string' && /^\/(?![\\/])/.test(redirect)) {
        const target = router.resolve(redirect);
        if (target.meta.requiresAuth && !target.meta.onboardingStep) return target.fullPath;
    }

    return '/area';
}
