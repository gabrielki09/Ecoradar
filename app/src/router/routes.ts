import type { RouteRecordRaw } from 'vue-router';
import { categoryRoutes } from '@/modules/categories/routes';

const routes: RouteRecordRaw[] = [
    {
        path: '/',
        component: () => import('@/layouts/MainLayout.vue'),
        children: [
            {
                path: '',
                name: 'home',
                component: () => import('@/modules/home/pages/HomePage.vue'),
            },
            ...categoryRoutes
        ],
    },
    {
        path: '/',
        component: () => import('@/layouts/auth/AuthLayout.vue'),
        children: [
            {
                path: 'login',
                name: 'login',
                meta: { public: true, guestOnly: true },
                component: () => import('@/modules/auth/LoginPage.vue'),
            },
            {
                path: 'cadastro',
                name: 'register',
                meta: { public: true, guestOnly: true },
                component: () => import('@/modules/auth/RegisterPage.vue'),
            },
        ],
    },
    {
        path: '/',
        component: () => import('@/layouts/AuthenticatedLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: 'area',
                name: 'user-home',
                component: () => import('@/modules/account/pages/UserHomePage.vue'),
            },
            {
                path: 'onboarding/collector',
                name: 'onboarding-collector',
                meta: { onboardingStep: 'collector' },
                component: () => import('@/modules/onboarding/pages/CollectorOnboardingPage.vue'),
            },
            {
                path: 'onboarding/company',
                name: 'onboarding-company',
                meta: { onboardingStep: 'company' },
                component: () => import('@/modules/onboarding/pages/CompanyOnboardingPage.vue'),
            },
        ],
    },

    {
        path: '/internal-server-error',
        component: () => import('@/pages/ErrorPages/InternalError.vue')
    },

    // A rota curinga precisa permanecer por último.
    {
        path: '/:catchAll(.*)*',
        component: () => import('@/pages/ErrorPages/ErrorNotFound.vue'),
    },
];

export default routes;
