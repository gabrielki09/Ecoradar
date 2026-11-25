import { api } from '@/boot/axios';
import type { ApiResponse } from '@/types/api/api.types';
import type {
    CollectionPoint, CreatePointPayload, Page, UpdatePointPayload, WasteCategory,
} from './types';

function pageOf<T>(value: Page<T> | undefined, label: string): Page<T> {
    if (!value || !Array.isArray(value.data)) {
        throw new Error(`${label}: resposta de paginação inválida.`);
    }
    return value;
}

export const pointService = {
    async list(page: number) {
        const response = await api.get<ApiResponse<{ collection_points: Page<CollectionPoint> }>>('/collection-points', {
            params: { page, per_page: 15, relationships: ['openingHours', 'collectionWasteCategory.wasteCategory'] },
        });
        return pageOf(response.data.data.collection_points, 'Pontos');
    },
    async mine(page: number, companyId?: number, includeTrashed = false) {
        const response = await api.get<ApiResponse<{ collection_points: Page<CollectionPoint> }>>(
            '/collection-points/for-provider',
            { params: { page, per_page: 15, ...(companyId ? { company_id: companyId } : {}), ...(includeTrashed ? { include_trashed: 1 } : {}) } },
        );
        return pageOf(response.data.data.collection_points, 'Meus pontos');
    },
    async detail(id: number, companyId?: number) {
        const response = await api.get<ApiResponse<{ collection_point: CollectionPoint }>>(
            `/collection-points/${id}`, { params: companyId ? { company_id: companyId } : {} },
        );
        return response.data.data.collection_point;
    },
    async create(payload: CreatePointPayload) {
        const response = await api.post<ApiResponse<{ collection_point: CollectionPoint }>>('/collection-points', payload);
        return response.data.data.collection_point;
    },
    async update(id: number, payload: UpdatePointPayload) {
        const response = await api.patch<ApiResponse<{ collection_point: CollectionPoint }>>(`/collection-points/${id}`, payload);
        return response.data.data.collection_point;
    },
    async remove(id: number, companyId?: number) {
        await api.delete(`/collection-points/${id}`, { params: companyId ? { company_id: companyId } : {} });
    },
    async recover(id: number, companyId?: number) {
        const response = await api.post<ApiResponse<CollectionPoint>>(
            `/collection-points/${id}/recovery`, {},
            { params: companyId ? { company_id: companyId } : {} },
        );
        return response.data.data;
    },
    async categories(): Promise<WasteCategory[]> {
        const items: WasteCategory[] = [];
        let page = 1;
        while (true) {
            const response = await api.get<ApiResponse<{ waste_categories: Page<WasteCategory> }>>(
                '/waste-categories', { params: { page } },
            );
            const result = pageOf(response.data.data.waste_categories, 'Categorias');
            items.push(...result.data);
            if (!result.next_page_url && page >= result.last_page) return items;
            if (result.current_page !== page || page > 100) {
                throw new Error('Não foi possível carregar todas as categorias.');
            }
            page++;
        }
    },
};
