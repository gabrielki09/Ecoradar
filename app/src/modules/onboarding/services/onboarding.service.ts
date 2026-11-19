import { api } from '@/boot/axios';
import type { ApiResponse } from '@/types/api/api.types';
import type {
    CollectorOnboardingResponse,
    CollectorOnboardingPayload,
    CompanyOnboardingPayload,
    CompanyOnboardingResponse,
} from '../types/onboarding.types';

export const onboardingService = {
    completeCollector(payload: CollectorOnboardingPayload) {
        return api.post<ApiResponse<CollectorOnboardingResponse>>('/onboarding/collector', payload);
    },

    completeCompany(payload: CompanyOnboardingPayload) {
        return api.post<ApiResponse<CompanyOnboardingResponse>>('/onboarding/company', payload);
    },
};
