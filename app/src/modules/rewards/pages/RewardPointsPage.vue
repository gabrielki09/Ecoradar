<template>
    <q-page class="bg-cream px-4 py-8">
        <main class="mx-auto max-w-[850px]">
            <RouterLink to="/area" class="text-eco-700">← Minha área</RouterLink>
            <h1 class="my-5 text-3xl font-extrabold text-eco-900">Pontos de recompensa</h1>
            <RewardBalance />
            <section v-if="rewards.context" class="mt-7" aria-labelledby="reward-history">
                <h2 id="reward-history" class="text-xl font-extrabold text-eco-900">Histórico de recompensas</h2>
                <p v-if="loading" role="status"><q-spinner color="primary" /> Carregando histórico…</p>
                <div v-else-if="error" role="alert" class="rounded-xl bg-red-50 p-4 text-negative">
                    {{ error }}
                    <q-btn flat color="primary" label="Tentar novamente" @click="load" />
                </div>
                <p v-else-if="!history?.data.length" class="rounded-xl border border-line bg-white p-5 text-muted">
                    Nenhuma recompensa recebida. Os lançamentos aparecem aqui quando uma coleta é concluída.
                </p>
                <ul v-else class="m-0 list-none divide-y divide-line rounded-2xl border border-line bg-white px-5">
                    <li v-for="entry in history.data" :key="entry.collection_request_id"
                        class="flex flex-wrap items-center justify-between gap-3 py-4">
                        <div>
                            <p class="m-0 font-bold text-eco-900">{{ entry.description }}</p>
                            <time :datetime="entry.created_at" class="text-sm text-muted">
                                {{ new Date(entry.created_at).toLocaleString('pt-BR') }}
                            </time>
                            <RouterLink v-if="entry.can_view_collection"
                                :to="{ name: 'collection-detail', params: { id: entry.collection_request_id }, query: { origem: 'recompensas' } }"
                                class="mt-1 block text-sm font-bold text-eco-700">Ver coleta #{{ entry.collection_request_id }}</RouterLink>
                            <span v-else class="mt-1 block text-sm text-muted">Coleta #{{ entry.collection_request_id }}</span>
                        </div>
                        <strong class="whitespace-nowrap text-eco-700">+{{ entry.points }} pontos</strong>
                    </li>
                </ul>
                <q-pagination v-if="!loading && !error && history && history.last_page > 1"
                    v-model="page" :max="history.last_page" :max-pages="5" direction-links
                    aria-label="Páginas do histórico" class="mt-5 flex justify-center" />
            </section>
        </main>
    </q-page>
</template>

<script setup lang="ts">
import { ref, watch, onBeforeUnmount } from 'vue';
import { useCollectionStore } from '@/modules/collection-requests/store';
import type { Page } from '@/modules/collection-requests/types';
import { readFormError } from '@/modules/auth/utils/form-errors';
import RewardBalance from '../components/RewardBalance.vue';
import { rewardService, type RewardEntry } from '../service';
import { useRewardStore } from '../store';

const collections = useCollectionStore();
const rewards = useRewardStore();
const page = ref(1);
const history = ref<Page<RewardEntry> | null>(null);
const loading = ref(false);
const error = ref('');
let sequence = 0;

async function load() {
    const current = ++sequence;
    history.value = null;
    error.value = '';
    loading.value = !!rewards.context;
    if (!rewards.context) return;
    try {
        const result = await rewardService.history(page.value, collections.selectedCompanyId);
        if (current === sequence) history.value = result;
    } catch (cause) {
        if (current === sequence) error.value = readFormError(cause, 'Não foi possível carregar o histórico.').message;
    } finally {
        if (current === sequence) loading.value = false;
    }
}

watch(() => rewards.context, () => { page.value = 1; }, { flush: 'sync' });
watch([page, () => rewards.context], load, { immediate: true });
onBeforeUnmount(() => { sequence++; });
</script>
