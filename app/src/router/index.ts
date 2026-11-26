import { defineRouter } from '#q-app';
import {
  createMemoryHistory,
  createRouter,
  createWebHashHistory,
  createWebHistory,
} from 'vue-router';

import routes from './routes';
import { useAuthStore } from '@/modules/auth/stores/auth.store';
import { usePointStore } from '@/modules/collection-points/store';

/*
 * If not building with SSR mode, you can
 * directly export the Router instantiation;
 *
 * The function below can be async too; either use
 * async/await or return a Promise which resolves
 * with the Router instance.
 */

export default defineRouter((/* { store, ssrContext } */) => {
  const createHistory = import.meta.env.QUASAR_SERVER
    ? createMemoryHistory
    : (import.meta.env.QUASAR_VUE_ROUTER_MODE === 'history' ? createWebHistory : createWebHashHistory);

  const Router = createRouter({
    scrollBehavior: (to, _from, savedPosition) => {
      if (savedPosition) return savedPosition;
      if (to.hash) return { el: to.hash, top: 80 };
      return { left: 0, top: 0 };
    },
    routes,

    // Leave this as is and make changes in quasar.conf.js instead!
    // quasar.conf.js -> build -> vueRouterMode
    // quasar.conf.js -> build -> publicPath
    history: createHistory(import.meta.env.QUASAR_VUE_ROUTER_BASE)
  });

  Router.beforeEach(async (to) => {
    const authStore = useAuthStore();
    await authStore.restoreSession();

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
      return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.guestOnly && authStore.isAuthenticated) {
      return authStore.destinationFor();
    }

    if (authStore.isAuthenticated && to.meta.requiresAuth) {
      const expectedDestination = authStore.destinationFor();
      const onboardingStep = to.meta.onboardingStep;

      if (onboardingStep && to.path !== expectedDestination) return expectedDestination;
      if (authStore.pendingOnboarding && to.path !== authStore.pendingOnboarding) return authStore.pendingOnboarding;
      if (to.meta.requesterOnly && !authStore.canRequest) return { name: 'user-home' };
      if (to.meta.providerOnly && !authStore.canProvide) return { name: 'user-home' };
      if (to.meta.collectorOnly && authStore.user?.profile_type !== 'collector') return { name: 'user-home' };
      if (to.meta.companyOnly && authStore.user?.profile_type !== 'company') return { name: 'user-home' };
      if (to.meta.pointManagement) {
        if (!authStore.canProvide) return { name: 'user-home' };
        if (authStore.user?.profile_type === 'company' && !usePointStore().selectedCompanyId) {
          return { name: 'user-home' };
        }
      }
      if (to.name === 'collection-list') {
        const area = String(to.params.area);
        if (!['all', 'available', 'for-me', 'mine'].includes(area)) return { name: 'user-home' };
        if (area === 'mine' && !authStore.canRequest) return { name: 'user-home' };
        if (area !== 'mine' && !authStore.canProvide) return { name: 'user-home' };
      }
      if ((to.name === 'collection-detail' || to.name === 'reward-points') && !(authStore.canRequest || authStore.canProvide)) return { name: 'user-home' };
    }
  });

  return Router;
});
