import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';
import { useAuthStore } from '@/modules/auth/stores/auth.store';
import { readFormError } from '@/modules/auth/utils/form-errors';
import { useCollectionStore } from '@/modules/collection-requests/store';
import { rewardService } from './service';

export const useRewardStore = defineStore('reward-points', () => {
    const auth = useAuthStore();
    const collections = useCollectionStore();
    const isCompany = computed(() => auth.user?.profile_type === 'company');
    const context = computed(() => {
        if (!auth.user || !(auth.canRequest || auth.canProvide)) return null;
        if (!isCompany.value) return `user/${auth.user.id}`;
        return collections.selectedCompanyId ? `company/${auth.user.id}/${collections.selectedCompanyId}` : null;
    });
    const balance = ref<number | null>(null);
    const loading = ref(false);
    const error = ref('');
    let sequence = 0;

    watch(context, () => {
        sequence++;
        balance.value = null;
        error.value = '';
        loading.value = false;
    }, { flush: 'sync' });

    async function refreshBalance() {
        if (!context.value) return;
        const current = ++sequence;
        loading.value = true;
        error.value = '';
        try {
            const result = await rewardService.balance(collections.selectedCompanyId);
            if (current === sequence) balance.value = result;
        } catch (cause) {
            if (current === sequence) error.value = readFormError(cause, 'Não foi possível carregar o saldo.').message;
        } finally {
            if (current === sequence) loading.value = false;
        }
    }

    return { context, isCompany, balance, loading, error, refreshBalance };
});
