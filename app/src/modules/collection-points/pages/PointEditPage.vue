<template>
    <q-page class="bg-cream px-4 py-8">
        <main class="mx-auto max-w-[800px]">
            <RouterLink to="/pontos/meus" class="text-eco-700">← Meus pontos</RouterLink>
            <h1 class="text-3xl font-extrabold text-eco-900">Editar ponto</h1>
            <p v-if="loading" role="status">Carregando ponto…</p>
            <p v-else-if="error" role="alert" class="text-negative">{{ error }}</p>
            <PointForm v-else-if="point" :key="`${point.id}-${store.selectedCompanyId}`" :initial="point" />
        </main>
    </q-page>
</template>

<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { readFormError } from '@/modules/auth/utils/form-errors';
import PointForm from '../components/PointForm.vue';
import { pointService } from '../service';
import { usePointStore } from '../store';
import type { CollectionPoint } from '../types';

const route = useRoute();
const store = usePointStore();
const point = ref<CollectionPoint | null>(null);
const loading = ref(false);
const error = ref('');

async function load() {
    loading.value = true;
    point.value = null;
    error.value = '';
    try {
        point.value = await pointService.detail(Number(route.params.id), store.selectedCompanyId ?? undefined);
    } catch (cause) {
        error.value = readFormError(cause, 'Ponto não encontrado ou sem acesso.').message;
    } finally {
        loading.value = false;
    }
}

watch(() => store.selectedCompanyId, () => void load());
onMounted(load);
</script>
