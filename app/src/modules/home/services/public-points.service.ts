import { api } from '@/boot/axios';
import type { ApiResponse } from '@/types/api/api.types';
import type { PublicPoint } from '../types/public-point.types';

interface Page<T> {
    data: T[];
    current_page: number;
    last_page: number;
}

export async function loadPublicPoints(): Promise<PublicPoint[]> {
    const points: PublicPoint[] = [];
    let page = 1;
    while (true) {
        const response = await api.get<ApiResponse<{ collection_points: Page<PublicPoint> }>>(
            '/collection-points/public', { params: { page, per_page: 100 } },
        );
        const result = response.data.data?.collection_points;
        if (!result || !Array.isArray(result.data) || result.current_page !== page ||
            !Number.isInteger(result.last_page) || result.last_page < page) {
            throw new Error('Resposta de pontos inválida.');
        }
        points.push(...result.data);
        if (page === result.last_page) return points;
        page++;
    }
}
