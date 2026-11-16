import { api } from '@/boot/axios';
import type { ApiResponse } from '@/types/api/api.types';
import type { CityIndexData, CityOption, CollectorProfileForm, WasteCategoryIndexData, WasteCategoryOption } from '../types/collector-profile.types';

export const collectorProfileService = {
    async getWasteCategories(): Promise<WasteCategoryOption[]> {
        const response = await api.get<ApiResponse<WasteCategoryIndexData>>('/waste-categories');
        return response.data.data.waste_categories.filter((category) => category.is_system);
    },

    async getCities(): Promise<CityOption[]> {
        const first = await api.get<ApiResponse<CityIndexData>>('/general/cities', { params: { page: 1, per_page: 100 } });
        const paginator = first.data.data.cities;
        const cities = [...paginator.data];
        for (let page = 2; page <= paginator.last_page; page += 1) {
            const response = await api.get<ApiResponse<CityIndexData>>('/general/cities', { params: { page, per_page: 100 } });
            cities.push(...response.data.data.cities.data);
        }
        return cities;
    },

    update(payload: CollectorProfileForm) {
        return api.put('/collector-profile', payload);
    },
};
