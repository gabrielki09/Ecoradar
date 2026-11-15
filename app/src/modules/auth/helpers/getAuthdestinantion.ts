import type { ApiResponse } from "@/types/api/api.types";
import type { AuthResponse } from "../types/auth.types";

const getAuthDestination = (data: ApiResponse<AuthResponse>['data']): string =>
{
    if (!data.onboarding.required) return '/area';

    switch (data.onboarding.next_step) {
        case 'collector':
            return '/onboarding/collector';

        case 'company':
            return '/onboarding/company';

        default:
            return '/area';
    }
};

export default getAuthDestination;
