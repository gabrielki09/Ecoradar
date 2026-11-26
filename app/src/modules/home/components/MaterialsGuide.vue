<template>
    <div>
        <div class="section-heading section-heading--split">
            <div>
                <span class="section-kicker">
                    O que descartar
                </span>
                <h2 :id="headingId">
                    {{ title }}
                </h2>
            </div>
            <slot name="action">
                <a href="#pontos">
                    Ver todos os materiais
                    <q-icon name="arrow_forward" />
                </a>
            </slot>
        </div>
        <div class="materials-grid">
            <MaterialCard
                v-for="material in wasteMaterials"
                :key="material.name"
                :material="material"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import MaterialCard from './MaterialCard.vue';
import { wasteMaterials } from '../data/home.data';

withDefaults(defineProps<{
    title?: string;
    headingId?: string;
}>(), {
    title: 'Encontre o destino de cada material',
});
</script>
