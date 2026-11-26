<template>
    <q-page class="bg-cream px-4 py-8">
        <main class="mx-auto max-w-[1120px]">
            <header class="point-list-header">
                <h1 class="point-list-title text-3xl font-extrabold text-eco-900">{{ mine ? 'Meus pontos de coleta' : 'Pontos de coleta' }}</h1>
                <nav class="point-navigation" aria-label="Pontos de coleta">
                    <q-btn
                        to="/pontos"
                        exact
                        unelevated
                        no-caps
                        text-color="black"
                        icon="format_list_bulleted"
                        label="Todos os pontos"
                        class="point-navigation-button"
                        :class="{ 'point-navigation-button--active': !mine }"
                        :aria-current="!mine ? 'page' : undefined"
                    />
                    <q-btn
                        v-if="canManage"
                        to="/pontos/meus"
                        exact
                        unelevated
                        no-caps
                        text-color="black"
                        icon="place"
                        label="Meus pontos"
                        class="point-navigation-button"
                        :class="{ 'point-navigation-button--active': mine }"
                        :aria-current="mine ? 'page' : undefined"
                    />
                    <q-btn
                        v-if="canManage"
                        to="/pontos/novo"
                        unelevated
                        no-caps
                        text-color="black"
                        icon="add_location_alt"
                        label="Criar ponto"
                        class="point-navigation-button"
                    />
                    <q-btn
                        v-if="!mine"
                        to="/#pontos"
                        unelevated
                        no-caps
                        text-color="black"
                        icon="map"
                        label="Ver no mapa"
                        class="point-navigation-button"
                    />
                </nav>
            </header>

            <div class="point-list-toolbar">
                <q-select
                    v-if="mine && auth.user?.profile_type === 'company'"
                    :model-value="store.selectedCompanyId"
                    :options="companyOptions"
                    label="Empresa responsável"
                    outlined
                    emit-value
                    map-options
                    class="point-list-company"
                    @update:model-value="store.selectCompany"
                />
                <q-checkbox v-if="mine" v-model="includeTrashed" label="Mostrar pontos excluídos" />
                <q-btn flat no-caps icon="refresh" label="Atualizar" @click="load" />
            </div>

            <p v-if="blocked" role="alert">Nenhuma empresa com papel responsável está vinculada à sua conta.</p>
            <div v-else-if="loading" role="status" class="py-12 text-center">
                <q-spinner color="primary" size="36px" />
                <p>Carregando pontos…</p>
            </div>
            <div v-else-if="error" role="alert" class="rounded-xl bg-red-50 p-5 text-negative">
                {{ error }}
                <q-btn flat label="Tentar novamente" @click="load" />
            </div>
            <div v-else-if="!records.length" class="rounded-xl bg-white p-8 text-center">
                <p>{{ mine ? 'Você ainda não tem pontos nesta lista.' : 'Ainda não há pontos cadastrados para consultar.' }}</p>
                <q-btn v-if="mine" flat color="primary" label="Criar ponto" to="/pontos/novo" />
            </div>
            <div v-else class="grid gap-3">
                <article v-for="point in records" :key="point.id" class="rounded-2xl border border-line bg-white p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="min-w-0 wrap-break-word">
                            <h2 class="m-0 text-lg font-bold text-eco-900">{{ point.name }}</h2>
                            <p class="m-0">{{ point.address }}, {{ point.neighborhood }} · CEP {{ formatCep(point.cep) }}</p>
                            <small>{{ point.deleted_at ? 'Excluído' : point.status }}</small>
                        </div>
                        <div class="flex gap-2">
                            <RouterLink v-if="mine && !point.deleted_at" :to="{ name: 'point-detail', params: { id: point.id }, query: store.selectedCompanyId ? { empresa: store.selectedCompanyId } : {} }" class="text-eco-700">Detalhes</RouterLink>
                            <q-btn v-if="mine && point.deleted_at" flat color="primary" label="Recuperar" :loading="workingId === point.id" :disable="workingId !== null" @click="recover(point.id)" />
                        </div>
                    </div>
                    <q-expansion-item
                        v-if="!mine"
                        label="Ver detalhes"
                        class="mt-2"
                    >
                        <div class="grid gap-2 px-4 pb-3 wrap-break-word">
                            <p class="m-0">{{ point.guidelines }}</p>
                            <p class="m-0"><strong>Contato</strong> {{ point.contact }}</p>
                            <p v-if="point.collection_waste_category?.length" class="m-0">
                                <strong>Materiais</strong>
                                {{ point.collection_waste_category.map(category => category.waste_category?.name).filter(Boolean).join(', ') }}
                            </p>
                            <p v-for="hour in point.opening_hours" :key="hour.opening_day" class="m-0">
                                {{ dayLabels[hour.opening_day] }} · {{ hour.opens_at.slice(0, 5) }}–{{ hour.closes_at.slice(0, 5) }}
                            </p>
                        </div>
                    </q-expansion-item>
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
import { dayLabels, type CollectionPoint } from '../types';

const route = useRoute();
const $q = useQuasar();
const auth = useAuthStore();
const store = usePointStore();
const canManage = computed(() => auth.canProvide &&
    (auth.user?.profile_type === 'collector' || store.companies.length > 0));
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
    if (workingId.value !== null) return;
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
    if (page.value === 1) {
        void load();
    } else {
        page.value = 1;
    }
});
watch(page, () => void load());
onBeforeUnmount(() => sequence++);
void load();
</script>

<style scoped>
.point-list-header {
    margin-bottom: 24px;
}

.point-list-title {
    margin: 0;
}

.point-navigation {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 20px;
}

.point-navigation-button {
    min-height: 44px;
    padding: 10px 16px;
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: 11px;
    font-weight: 600;
    text-decoration: none;
}

.point-navigation-button:hover,
.point-navigation-button--active {
    background: var(--eco-50);
    border-color: var(--eco-600);
}

.point-navigation-button:focus-visible {
    outline: 2px solid var(--eco-600);
    outline-offset: 3px;
}

.point-list-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
}

.point-list-company {
    width: 100%;
    max-width: 320px;
}
</style>
