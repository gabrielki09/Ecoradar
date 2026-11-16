import { computed, ref } from 'vue';
import { defineStore } from 'pinia';
import { LocalStorage } from 'quasar';
import axios from 'axios';
import { authService } from '../services/auth.service';
import type { AuthResponse, LoginPayload, RegisterPayload } from '../types/auth.types';
import type { UserResource } from '../types/user.types';
import { setUnauthorizedHandler } from '@/boot/axios';

export type SessionStatus = 'checking' | 'authenticated' | 'unauthenticated';

const TOKEN_KEY = 'authToken';

export const useAuthStore = defineStore('auth', () => {
    const status = ref<SessionStatus>('checking');
    const user = ref<UserResource | null>(null);
    const onboarding = ref<{ required: boolean; next_step: 'collector' | 'company' | null } | null>(null);
    let restorePromise: Promise<void> | null = null;

    const isAuthenticated = computed(() => status.value === 'authenticated' && user.value !== null);
    const isChecking = computed(() => status.value === 'checking');

    function clearSession() {
        LocalStorage.remove(TOKEN_KEY);
        user.value = null;
        onboarding.value = null;
        status.value = 'unauthenticated';
    }

    function acceptAuthentication(payload: AuthResponse) {
        LocalStorage.set(TOKEN_KEY, payload.token);
        user.value = payload.user;
        onboarding.value = payload.onboarding;
        status.value = 'authenticated';
    }

    function destinationFor(currentUser = user.value, response?: AuthResponse): string {
        if (!currentUser) return '/login';
        if (currentUser.onboarding_completed) return '/area';

        const nextStep = response?.onboarding.next_step ?? onboarding.value?.next_step;
        if (nextStep === 'collector' || (!nextStep && currentUser.profile_type === 'collector')) {
            return '/onboarding/collector';
        }
        if (nextStep === 'company' || (!nextStep && currentUser.profile_type === 'company')) {
            return '/onboarding/company';
        }

        return '/area';
    }

    async function restoreSession() {
        if (status.value !== 'checking') return;
        if (restorePromise) return restorePromise;

        restorePromise = (async () => {
            const token = LocalStorage.getItem<string>(TOKEN_KEY);
            if (!token || token === 'undefined') {
                clearSession();
                return;
            }

            try {
                const response = await authService.me();
                user.value = response.data.data.user;
                onboarding.value = response.data.data.onboarding;
                status.value = 'authenticated';
            } catch (error) {
                if (axios.isAxiosError(error) && error.response?.status === 401) {
                    clearSession();
                    return;
                }
                user.value = null;
                status.value = 'unauthenticated';
            }
        })().finally(() => {
            restorePromise = null;
        });

        return restorePromise;
    }

    async function refreshCurrentUser() {
        const response = await authService.me();
        user.value = response.data.data.user;
        onboarding.value = response.data.data.onboarding;
        status.value = 'authenticated';
        return user.value;
    }

    async function login(payload: LoginPayload) {
        const response = await authService.login(payload);
        acceptAuthentication(response.data.data);
        return response.data.data;
    }

    async function register(payload: RegisterPayload) {
        const response = await authService.register(payload);
        acceptAuthentication(response.data.data);
        return response.data.data;
    }

    async function logout() {
        try {
            await authService.logout();
        } finally {
            clearSession();
        }
    }

    setUnauthorizedHandler(clearSession);

    return {
        status,
        user,
        onboarding,
        isAuthenticated,
        isChecking,
        clearSession,
        destinationFor,
        restoreSession,
        refreshCurrentUser,
        login,
        register,
        logout,
    };
});
