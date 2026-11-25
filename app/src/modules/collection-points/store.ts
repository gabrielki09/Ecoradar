import { computed, ref, watch } from 'vue';
import { defineStore } from 'pinia';
import { SessionStorage } from 'quasar';
import { useAuthStore } from '@/modules/auth/stores/auth.store';

export const usePointStore = defineStore('collection-points', () => {
    const auth = useAuthStore();
    const selectedCompanyId = ref<number | null>(null);
    const companies = computed(() => auth.user?.profile_type === 'company'
        ? (auth.user.companies ?? []).filter(company => company.role === 'responsible') : []);
    const revision = ref(0);

    function selectCompany(id: number | null) {
        if (id !== null && !companies.value.some(company => company.id === id)) return;
        if (selectedCompanyId.value === id) return;
        selectedCompanyId.value = id;
        revision.value++;
        if (auth.user && id !== null) SessionStorage.set(`ecoradar-point-company-${auth.user.id}`, id);
    }

    watch([() => auth.user?.id, companies], ([userId], previous) => {
        if (previous && previous[0] !== userId) selectedCompanyId.value = null;
        if (!companies.value.some(company => company.id === selectedCompanyId.value)) {
            const saved = SessionStorage.getItem<number>(`ecoradar-point-company-${userId}`);
            selectCompany(companies.value.find(company => company.id === saved)?.id ?? companies.value[0]?.id ?? null);
        }
    }, { immediate: true });

    return { companies, selectedCompanyId, revision, selectCompany, invalidate: () => revision.value++ };
});
