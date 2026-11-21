<template>
    <q-page class="bg-cream px-4 py-8">
        <main class="mx-auto max-w-[800px]">
            <RouterLink to="/pontos/meus" class="text-eco-700">← Meus pontos</RouterLink>
            <h1 class="text-3xl font-extrabold text-eco-900">Novo ponto de coleta</h1>
            <PointForm />
        </main>
    </q-page>
</template>

<script setup lang="ts">
import PointForm from '../components/PointForm.vue';
</script>
