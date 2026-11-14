import { api } from "@/boot/axios";

import type {
    Category,
    CategoryPayload
} from '../types/category.types'
import { ApiResponse } from "@/types/api/api.types";

const CATEGORY_PREFIX_URL = '/categories';

export const categoryService = {
    async getAll(): Promise<Category[]> {
        const { data } = await api.get<ApiResponse<Category[]>>(CATEGORY_PREFIX_URL);

        return data.data;
    },

    async create(payload: CategoryPayload): Promise<Category> {
        const { data } = await api.post<ApiResponse<Category>>(
            CATEGORY_PREFIX_URL,
            payload
        );

        return data.data;
    },

    async delete(id: number): Promise<void> {
        await api.delete(`${CATEGORY_PREFIX_URL}/delete/${id}`);
    }
};
