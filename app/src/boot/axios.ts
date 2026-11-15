import { defineBoot } from '#q-app';
import axios, { type AxiosInstance } from 'axios';
import { LocalStorage } from 'quasar';
import { ApiErrorResponse } from '@/types/api/api.types';

axios.defaults.withCredentials = false;
declare module 'vue' {
    interface ComponentCustomProperties {
        $axios: AxiosInstance;
        $api: AxiosInstance;
    }
}

// Be careful when using SSR for cross-request state pollution
// due to creating a Singleton instance here;
// If any client changes this (global) instance, it might be a
// good idea to move this instance creation inside of the
// "export default () => {}" function below (which runs individually
// for each client)

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL ?? '',
    headers: {
        Accept: 'application/json',
        "Content-Type": 'application/json'
    }
});

const apiCep = axios.create({
    baseURL: import.meta.env.VITE_API_CEP ?? '',
    headers: {
        Accept: 'application/json',
        "Content-Type": 'application/json'
    }
});

const apiCnpj = axios.create({
    baseURL: import.meta.env.VITE_API_CNPJ ?? '',
    headers: {
        Accept: 'application/json',
        "Content-Type": 'application/json'
    }
});

let unauthorizedHandler: (() => void) | undefined;

export function setUnauthorizedHandler(handler: () => void) {
    unauthorizedHandler = handler;
}

const showConsoleMessage = (error: any) => {
    if (import.meta.env.VITE_APP_ENV === "dev")
    {
        const res: ApiErrorResponse | undefined = error.response?.data;

        console.error('Erro:', res ?? error);

    };
};

export default defineBoot(({ app }) => {
    api.interceptors.request.use(
        (config) => {
            const token = LocalStorage.getItem("authToken");

            if (token) config.headers.Authorization = `Bearer ${token}`;

            return config;
        },
        (error) => {
            return Promise.reject(error);
        }
    );

    api.interceptors.response.use(
        (response) => response,
        (error) => {
            if (error.response?.status === 401)
            {
                LocalStorage.remove('authToken');
                unauthorizedHandler?.();

                return Promise.reject(error);
            };

            if (error.response) {
                showConsoleMessage(error);

            } else {
                showConsoleMessage(error);
            };

            return Promise.reject(error);
        }
    );

    app.config.globalProperties.$axios = axios;
    // ^ ^ ^ this will allow you to use this.$axios (for Vue Options API form)
    //       so you won't necessarily have to import axios in each vue file

    app.config.globalProperties.$api = api;
    // ^ ^ ^ this will allow you to use this.$api (for Vue Options API form)
    //       so you can easily perform requests against your app's API
});

export { api, apiCnpj, apiCep };
