import { api } from '@/boot/axios';
import type { ApiResponse } from '@/types/api/api.types';
import type {
    CollectorOnboardingResponse,
    CompanyOnboardingPayload,
    CompanyOnboardingResponse,
} from '../types/onboarding.types';

export const onboardingService = {
    completeCollector() {
        return api.post<ApiResponse<CollectorOnboardingResponse>>('/onboarding/collector', {});
    },

    completeCompany(payload: CompanyOnboardingPayload) {
        return api.post<ApiResponse<CompanyOnboardingResponse>>('/onboarding/company', payload);
    },
};
