<template>
    <section class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6" aria-label="Saldo de pontos de recompensa">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="grid size-11 place-items-center rounded-xl bg-eco-100 text-eco-700">
                    <q-icon name="stars" size="26px" />
                </span>
                <div>
                    <h2 class="m-0 text-lg font-extrabold text-eco-900">
                        {{ dashboard ? 'Pontos de recompensa' : rewards.isCompany ? 'Saldo da empresa' : 'Seu saldo' }}
                    </h2>
                    <p v-if="company" class="m-0 text-sm text-muted">Empresa · {{ company.company_name }}</p>
                </div>
            </div>
            <q-select v-if="collections.companies.length > 1"
                :model-value="collections.selectedCompanyId" :options="collections.companies"
                option-label="company_name" option-value="id" label="Empresa" outlined dense emit-value map-options
                class="w-full sm:max-w-xs" @update:model-value="collections.selectCompany" />
        </div>
        <p v-if="!rewards.context" class="mt-4 text-muted">Nenhuma empresa vinculada disponível para consulta.</p>
        <p v-else-if="rewards.loading" role="status" class="mt-4"><q-spinner color="primary" /> Carregando saldo…</p>
        <div v-else-if="rewards.error" role="alert" class="mt-4 text-negative">
            {{ rewards.error }}
            <q-btn flat color="primary" label="Tentar novamente" @click="rewards.refreshBalance" />
        </div>
        <p v-else-if="rewards.balance !== null" class="mb-0 mt-4 text-eco-900">
            <strong class="text-3xl font-extrabold">{{ rewards.balance.toLocaleString('pt-BR') }}</strong>
            {{ rewards.balance === 1 ? 'ponto' : 'pontos' }}
        </p>
        <RouterLink v-if="dashboard" :to="{ name: 'reward-points' }" class="mt-3 inline-block font-bold text-eco-700">
            Ver histórico de recompensas
        </RouterLink>
        <p v-else class="mb-0 mt-3 text-sm text-muted">Os pontos acumulados poderão ser usados em benefícios futuros.</p>
    </section>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue';
import { useCollectionStore } from '@/modules/collection-requests/store';
import { useRewardStore } from '../store';

defineProps<{ dashboard?: boolean }>();
const collections = useCollectionStore();
const rewards = useRewardStore();
const company = computed(() => collections.companies.find(item => item.id === collections.selectedCompanyId));
watch(() => rewards.context, rewards.refreshBalance, { immediate: true });
</script>
