import { api } from '@/boot/axios';
import type { Page } from '@/modules/collection-requests/types';
import type { ApiResponse } from '@/types/api/api.types';

export interface RewardEntry {
    collection_request_id: number;
    points: number;
    description: string;
    created_at: string;
    can_view_collection: boolean;
}

export const rewardService = {
    async balance(companyId: number | null) {
        const { data } = await api.get<ApiResponse<{ balance: number }>>('/reward-points', {
            params: { company_id: companyId ?? undefined },
        });
        return data.data.balance;
    },

    async history(page: number, companyId: number | null) {
        const { data } = await api.get<ApiResponse<{ history: Page<RewardEntry> }>>('/reward-points/history', {
            params: { page, per_page: 10, company_id: companyId ?? undefined },
        });
        return data.data.history;
    },
};
