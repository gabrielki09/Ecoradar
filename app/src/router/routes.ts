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
                path: 'area/perfil-coleta',
                name: 'collector-profile',
                meta: { collectorOnly: true },
                component: () => import('@/modules/collector-profile/pages/CollectorProfilePage.vue'),
            },
            {
                path: 'pontos',
                name: 'point-list',
                component: () => import('@/modules/collection-points/pages/PointListPage.vue'),
            },
            {
                path: 'pontos/meus',
                name: 'point-mine',
                meta: { pointManagement: true },
                component: () => import('@/modules/collection-points/pages/PointListPage.vue'),
            },
            {
                path: 'pontos/novo',
                name: 'point-create',
                meta: { pointManagement: true },
                component: () => import('@/modules/collection-points/pages/PointCreatePage.vue'),
            },
            {
                path: 'pontos/meus/:id/editar',
                name: 'point-edit',
                meta: { pointManagement: true },
                component: () => import('@/modules/collection-points/pages/PointEditPage.vue'),
            },
            {
                path: 'pontos/meus/:id',
                name: 'point-detail',
                meta: { pointManagement: true },
                component: () => import('@/modules/collection-points/pages/PointDetailPage.vue'),
            },
            { path: 'area/pontos', redirect: { name: 'point-list' } },
            { path: 'area/pontos/meus', redirect: { name: 'point-mine' } },
            { path: 'area/pontos/novo', redirect: { name: 'point-create' } },
            { path: 'area/pontos/meus/:id/editar', redirect: to => ({ name: 'point-edit', params: to.params }) },
            { path: 'area/pontos/meus/:id', redirect: to => ({ name: 'point-detail', params: to.params }) },
            { path: 'area/coletas/nova', name: 'collection-create', meta: { requesterOnly: true }, component: () => import('@/modules/collection-requests/pages/CollectionCreatePage.vue') },
            { path: 'area/coletas/detalhe/:id', name: 'collection-detail', component: () => import('@/modules/collection-requests/pages/CollectionDetailPage.vue') },
            { path: 'area/coletas/:area', name: 'collection-list', component: () => import('@/modules/collection-requests/pages/CollectionListPage.vue') },
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
