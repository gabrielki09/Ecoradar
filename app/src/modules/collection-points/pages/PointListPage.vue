<template>
    <q-page class="bg-cream px-4 py-8">
        <main class="mx-auto max-w-[1120px]">
            <h1 class="text-3xl font-extrabold text-eco-900">{{ mine ? 'Meus pontos de coleta' : 'Pontos de coleta' }}</h1>
            <nav class="mt-4 mb-6 flex flex-wrap gap-3" aria-label="Pontos de coleta">
                <RouterLink to="/pontos" class="text-eco-700">Todos os pontos</RouterLink>
                <RouterLink v-if="auth.canProvide" to="/pontos/meus" class="text-eco-700">Meus pontos</RouterLink>
                <RouterLink v-if="auth.canProvide" to="/pontos/novo" class="text-eco-700">Criar ponto</RouterLink>
            </nav>

            <q-select
                v-if="mine && auth.user?.profile_type === 'company'"
                :model-value="store.selectedCompanyId"
                :options="companyOptions"
                label="Empresa responsável"
                outlined
                emit-value
                map-options
                class="mb-4 max-w-sm"
                @update:model-value="store.selectCompany"
            />
            <q-checkbox v-if="mine" v-model="includeTrashed" label="Mostrar pontos excluídos" class="mb-4" />
            <q-btn flat icon="refresh" label="Atualizar" class="mb-4" @click="load" />

            <p v-if="blocked" role="alert">Nenhuma empresa com papel responsável está vinculada à sua conta.</p>
            <div v-else-if="loading" role="status" class="py-12 text-center">
                <q-spinner color="primary" size="36px" />
                <p>Carregando pontos…</p>
            </div>
            <div v-else-if="error" role="alert" class="rounded-xl bg-red-50 p-5 text-negative">
                {{ error }}
                <q-btn flat label="Tentar novamente" @click="load" />
            </div>
            <p v-else-if="!records.length" class="rounded-xl bg-white p-8 text-center">Nenhum ponto encontrado.</p>
            <div v-else class="grid gap-3">
                <article v-for="point in records" :key="point.id" class="rounded-2xl border border-line bg-white p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="m-0 text-lg font-bold text-eco-900">{{ point.name }}</h2>
                            <p class="m-0">{{ point.address }}, {{ point.neighborhood }} · CEP {{ formatCep(point.cep) }}</p>
                            <small>{{ point.deleted_at ? 'Excluído' : point.status }}</small>
                        </div>
                        <div class="flex gap-2">
                            <RouterLink v-if="mine && !point.deleted_at" :to="`/pontos/meus/${point.id}`" class="text-eco-700">Detalhes</RouterLink>
                            <q-btn v-if="mine && point.deleted_at" flat color="primary" label="Recuperar" :loading="workingId === point.id" @click="recover(point.id)" />
                        </div>
                    </div>
                    <p v-if="actionErrorId === point.id" role="alert" class="text-negative">{{ actionError }}</p>
                </article>
            </div>
            <q-pagination v-if="!loading && !error && lastPage > 1" v-model="page" :max="lastPage" class="mt-6 justify-center" />
        </main>
    </q-page>
</template>

<script setup lang="ts">
import { computed, ref, watch, onBeforeUnmount } from 'vue';
import { useRoute } from 'vue-router';
import { useQuasar } from 'quasar';
import { readFormError } from '@/modules/auth/utils/form-errors';
import { formatCep } from '@/utils/cep';
import { useAuthStore } from '@/modules/auth/stores/auth.store';
import { pointService } from '../service';
import { usePointStore } from '../store';
import type { CollectionPoint } from '../types';

const route = useRoute();
const $q = useQuasar();
const auth = useAuthStore();
const store = usePointStore();
const mine = computed(() => route.name === 'point-mine');
const blocked = computed(() => mine.value && auth.user?.profile_type === 'company' && !store.selectedCompanyId);
const companyOptions = computed(() => store.companies.map(company => ({ label: company.company_name, value: company.id })));
const records = ref<CollectionPoint[]>([]);
const includeTrashed = ref(false);
const page = ref(1);
const lastPage = ref(1);
const loading = ref(false);
const error = ref('');
const actionError = ref('');
const actionErrorId = ref<number | null>(null);
const workingId = ref<number | null>(null);
let sequence = 0;

async function load() {
    const current = ++sequence;
    if (blocked.value) return;
    loading.value = true;
    error.value = '';
    try {
        const result = mine.value
            ? await pointService.mine(page.value, store.selectedCompanyId ?? undefined, includeTrashed.value)
            : await pointService.list(page.value);
        if (current !== sequence) return;
        records.value = result.data;
        lastPage.value = result.last_page;
    } catch (cause) {
        if (current === sequence) error.value = readFormError(cause, 'Não foi possível carregar os pontos.').message;
    } finally {
        if (current === sequence) loading.value = false;
    }
}

async function recover(id: number) {
    workingId.value = id;
    actionErrorId.value = null;
    try {
        await pointService.recover(id, store.selectedCompanyId ?? undefined);
        store.invalidate();
        $q.notify({ type: 'positive', message: 'Ponto recuperado.' });
        await load();
    } catch (cause) {
        actionErrorId.value = id;
        actionError.value = readFormError(cause, 'Não foi possível recuperar o ponto.').message;
    } finally {
        workingId.value = null;
    }
}

watch([mine, includeTrashed, () => store.selectedCompanyId], () => {
    page.value = 1;
    void load();
});
watch(page, () => void load());
onBeforeUnmount(() => sequence++);
void load();
</script>
