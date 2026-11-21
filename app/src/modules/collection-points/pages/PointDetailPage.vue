<template>
    <q-page class="bg-cream px-4 py-8">
        <main class="mx-auto max-w-[800px]">
            <RouterLink to="/pontos/meus" class="text-eco-700">← Meus pontos</RouterLink>
            <div v-if="loading" role="status"><q-spinner color="primary" /> Carregando ponto…</div>
            <div v-else-if="error" role="alert" class="text-negative">
                {{ error }}
                <q-btn flat label="Tentar novamente" @click="load" />
            </div>
            <template v-else-if="point">
                <h1 class="text-3xl font-extrabold text-eco-900">{{ point.name }}</h1>
                <article class="grid gap-3 rounded-2xl bg-white p-6">
                    <p>{{ point.address }}, {{ point.neighborhood }} · CEP {{ formatCep(point.cep) }}</p>
                    <p><strong>Orientações:</strong> {{ point.guidelines }}</p>
                    <p><strong>Contato:</strong> {{ point.contact }}</p>
                    <p><strong>Status:</strong> {{ point.status }}</p>
                    <h2 class="text-xl font-bold">Horários</h2>
                    <p v-for="hour in point.opening_hours" :key="hour.opening_day" class="m-0">
                        {{ dayLabels[Number(hour.opening_day)] }}: {{ hour.opens_at }}–{{ hour.closes_at }}
                    </p>
                    <h2 class="text-xl font-bold">Materiais recebidos</h2>
                    <p v-for="category in point.collection_waste_category" :key="category.waste_category_id" class="m-0">
                        {{ categoryNames[category.waste_category_id] ?? `Categoria #${category.waste_category_id}` }}
                    </p>
                    <div class="flex gap-3">
                        <RouterLink :to="`/pontos/meus/${point.id}/editar`" class="text-eco-700">Editar</RouterLink>
                        <q-btn flat color="negative" label="Excluir" @click="confirming = true" />
                    </div>
                    <p v-if="actionError" role="alert" class="text-negative">{{ actionError }}</p>
                </article>
            </template>
            <q-dialog v-model="confirming">
                <q-card class="p-5">
                    <q-card-section>Excluir este ponto de coleta?</q-card-section>
                    <q-card-actions align="right">
                        <q-btn flat label="Cancelar" v-close-popup />
                        <q-btn color="negative" label="Excluir ponto" :loading="removing" @click="remove" />
                    </q-card-actions>
                </q-card>
            </q-dialog>
        </main>
    </q-page>
</template>

<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useQuasar } from 'quasar';
import { readFormError } from '@/modules/auth/utils/form-errors';
import { formatCep } from '@/utils/cep';
import { pointService } from '../service';
import { usePointStore } from '../store';
import { dayLabels, type CollectionPoint } from '../types';

const route = useRoute();
const router = useRouter();
const $q = useQuasar();
const store = usePointStore();
const point = ref<CollectionPoint | null>(null);
const categoryNames = ref<Record<number, string>>({});
const loading = ref(false);
const error = ref('');
const actionError = ref('');
const confirming = ref(false);
const removing = ref(false);

async function load() {
    loading.value = true;
    error.value = '';
    point.value = null;
    try {
        point.value = await pointService.detail(Number(route.params.id), store.selectedCompanyId ?? undefined);
    } catch (cause) {
        error.value = readFormError(cause, 'Ponto não encontrado ou sem acesso.').message;
    } finally {
        loading.value = false;
    }
}

async function remove() {
    if (!point.value) return;
    removing.value = true;
    actionError.value = '';
    try {
        await pointService.remove(point.value.id, store.selectedCompanyId ?? undefined);
        store.invalidate();
        $q.notify({ type: 'positive', message: 'Ponto excluído.' });
        await router.replace('/pontos/meus');
    } catch (cause) {
        confirming.value = false;
        actionError.value = readFormError(cause, 'Não foi possível excluir o ponto.').message;
    } finally {
        removing.value = false;
    }
}

watch(() => store.selectedCompanyId, () => void load());
onMounted(() => {
    void load();
    void pointService.categories().then(items => {
        categoryNames.value = Object.fromEntries(items.map(item => [item.id, item.name]));
    }).catch(() => {});
});
</script>
