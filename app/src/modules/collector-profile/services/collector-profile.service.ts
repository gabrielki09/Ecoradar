import { api } from '@/boot/axios';
import type { ApiResponse } from '@/types/api/api.types';
import type { CollectorProfileData, CollectorProfileForm, WasteCategoryIndexData, WasteCategoryOption } from '../types/collector-profile.types';

export const collectorProfileService = {
    async getAllWasteCategories(): Promise<WasteCategoryOption[]> {
        const first = await api.get<ApiResponse<WasteCategoryIndexData>>('/waste-categories', { params: { page: 1, per_page: 100 } });
        const result = first.data.data.waste_categories;
        if (Array.isArray(result)) return result;
        const categories = [...result.data];
        for (let page = 2; page <= result.last_page; page++) {
            const response = await api.get<ApiResponse<WasteCategoryIndexData>>('/waste-categories', { params: { page, per_page: 100 } });
            const next = response.data.data.waste_categories;
            categories.push(...(Array.isArray(next) ? next : next.data));
        }
        return categories;
    },
    async getWasteCategories(): Promise<WasteCategoryOption[]> {
        return (await this.getAllWasteCategories()).filter(category => category.is_system);
    },

    async getProfile(): Promise<CollectorProfileData> {
        const response = await api.get<ApiResponse<{ collector_profile: CollectorProfileData }>>('/collector-profile');
        return response.data.data.collector_profile;
    },

    update(payload: CollectorProfileForm) {
        return api.put('/collector-profile', payload);
    },
};
