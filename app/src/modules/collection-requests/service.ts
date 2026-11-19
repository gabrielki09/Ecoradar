import { api } from '@/boot/axios';
import type { ApiResponse } from '@/types/api/api.types';
import type { Area, CollectionItem, CollectionRequest, CollectionStatus, Page } from './types';

export const collectionService = {
  async list(area: Area, page: number, perPage: number, status?: CollectionStatus | '', companyId?: number): Promise<Page<CollectionRequest>> {
    const path = `/collection-requests/${area}`;
    const params: Record<string, unknown> = { page, per_page: perPage };
    if (companyId && (area === 'available' || area === 'for-me')) params.company_id = companyId;
    if (status) params.filtersV1 = [{ field: 'status', filterType: 'EQUAL', filterValue: status }];
    const response = await api.get<ApiResponse<{ collection_requests?: Page<CollectionRequest>; collection_request?: Page<CollectionRequest> }>>(path, { params });
    const result = response.data.data.collection_requests ?? response.data.data.collection_request;
    if (!result || !Array.isArray(result.data)) throw new Error('A listagem retornou um formato inesperado.');
    return result;
  },
  async detail(id: number) {
    const response = await api.get<ApiResponse<{ collection_request: CollectionRequest }>>(`/collection-requests/${id}`);
    const result = response.data.data.collection_request;
    if (!result) throw new Error('Solicitação não encontrada.');
    return result;
  },
  async create(payload: { city_id: number; cep: string; neighborhood: string; address: string; items: CollectionItem[] }) {
    const response = await api.post<ApiResponse<{ collection_request: CollectionRequest }>>('/collection-requests', payload);
    return response.data.data.collection_request;
  },
  async transition(action: 'accept' | 'collected' | 'complete' | 'cancel', id: number, companyId?: number, reason?: string) {
    const payload: Record<string, unknown> = { collection_request_id: id };
    if (companyId) payload.company_id = companyId;
    if (reason !== undefined) payload.cancellation_reason = reason;
    const response = await api.post<ApiResponse<{ collection_request: CollectionRequest }>>(`/collection-requests/${action}`, payload);
    return response.data.data.collection_request;
  },
};
