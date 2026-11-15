import { api } from "@/boot/axios";
import type {
    LoginPayload,
    RegisterPayload,
    AuthResponse,
    MeResponse,
} from '../types/auth.types'
import type { ApiResponse } from "@/types/api/api.types";

export const authService = {
    login(payload: LoginPayload) {
        return api.post<ApiResponse<AuthResponse>>('/auth/login', payload);
    },

    register(payload: RegisterPayload) {
        return api.post<ApiResponse<AuthResponse>>('/auth/register', payload);
    },

    logout() {
        return api.post('/auth/logout');
    },

    me() {
        return api.get<ApiResponse<MeResponse>>('/auth/me');
    }
}
