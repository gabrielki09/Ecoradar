import { defineStore } from 'pinia';
import { SessionStorage } from 'quasar';
import { computed, ref, watch } from 'vue';
import type { Area } from './types';
import { useAuthStore } from '@/modules/auth/stores/auth.store';

export const useCollectionStore = defineStore('collection-requests', () => {
  const auth = useAuthStore();
  const selectedCompanyId = ref<number | null>(null);
  const companies = computed(() => auth.user?.profile_type === 'company' ? auth.user.companies ?? [] : []);
  const revision = ref(0);
  // Apenas IDs de vínculos recebidos da sessão autenticada podem ser selecionados.
  function selectCompany(id: number | null) {
    if (id !== null && !companies.value.some(company => company.id === id)) return;
    if (selectedCompanyId.value === id) return;
    selectedCompanyId.value = id;
    revision.value++;
    if (auth.user && id !== null) SessionStorage.set(`ecoradar-request-company-${auth.user.id}`, id);
  }
  watch([() => auth.user?.id, companies], ([userId], previous) => {
    if (previous && previous[0] !== userId) {
      selectedCompanyId.value = null;
      revision.value++;
    }
    if (!companies.value.some(company => company.id === selectedCompanyId.value)) {
      const saved = SessionStorage.getItem<number>(`ecoradar-request-company-${userId}`);
      selectCompany(companies.value.find(company => company.id === saved)?.id ?? companies.value[0]?.id ?? null);
    }
  }, { immediate: true });
  function invalidate(_areas?: Area[]) { revision.value++; }
  return { companies, selectedCompanyId, revision, selectCompany, invalidate };
});
