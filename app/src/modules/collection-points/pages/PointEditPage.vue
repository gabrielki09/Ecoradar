<template>
    <q-page class="bg-cream px-4 py-8">
        <main class="mx-auto max-w-[800px]">
            <RouterLink
                :to="{ name: 'point-detail', params: { id: String(route.params.id) }, query: route.query }"
                class="text-eco-700"
            >
                ← Detalhes do ponto
            </RouterLink>
            <h1 class="text-3xl font-extrabold text-eco-900">Editar ponto</h1>
            <p v-if="loading" role="status">Carregando ponto…</p>
            <div v-else-if="error" role="alert" class="text-negative">
                {{ error }}
                <q-btn flat label="Tentar novamente" @click="load" />
            </div>
            <PointForm
                v-else-if="point"
                :key="point.id"
                :initial="point"
            />
        </main>
    </q-page>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { readFormError } from '@/modules/auth/utils/form-errors';
import PointForm from '../components/PointForm.vue';
import { pointService } from '../service';
import { usePointStore } from '../store';
import type { CollectionPoint } from '../types';

const route = useRoute();
const store = usePointStore();
const companyId = computed(() => route.query.empresa
    ? Number(route.query.empresa)
    : store.selectedCompanyId ?? undefined);
let sequence = 0;
const point = ref<CollectionPoint | null>(null);
const loading = ref(false);
const error = ref('');

async function load() {
    const current = ++sequence;
    loading.value = true;
    point.value = null;
    error.value = '';
    try {
        const result = await pointService.detail(Number(route.params.id), companyId.value);
        if (current === sequence) point.value = result;
    } catch (cause) {
        if (current === sequence) error.value = readFormError(cause, 'Ponto não encontrado ou sem acesso.').message;
    } finally {
        if (current === sequence) loading.value = false;
    }
}

watch([() => route.params.id, companyId], () => {
    if (route.params.id) void load();
}, { immediate: true });
onBeforeUnmount(() => sequence++);

</script>
