import axios from 'axios';
import type { ApiErrorResponse } from '@/types/api/api.types';

export function readFormError(error: unknown, fallback: string) {
    if (!axios.isAxiosError<ApiErrorResponse>(error)) {
        return { message: fallback, fields: {} as Record<string, string> };
    }

    if (!error.response) {
        return { message: 'Não foi possível conectar ao servidor.', fields: {} as Record<string, string> };
    }

    const body = error.response.data;
    const fields = Object.fromEntries(
        Object.entries(body.errors ?? {}).map(([field, messages]) => [field, messages[0] ?? fallback]),
    );

    return { message: body.message || fallback, fields };
}
