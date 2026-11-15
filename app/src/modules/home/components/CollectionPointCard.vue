<template>
    <article class="point-card">
        <div class="point-card__top">
            <div class="point-card__icon"><q-icon :name="point.icon" size="26px" /></div>
            <span><q-icon name="near_me" /> {{ point.distance }}</span>
        </div>
        <h3>{{ point.name }}</h3>
        <p class="point-card__address"><q-icon name="location_on" /> {{ point.address }}</p>
        <p class="point-card__schedule"><span></span>{{ point.schedule }}</p>
        <div class="point-card__tags"><span v-for="material in point.materials" :key="material">{{ material }}</span></div>
        <q-btn flat no-caps label="Ver detalhes" icon-right="arrow_forward" class="point-card__button" />
    </article>
</template>

<script setup lang="ts">
    import type { CollectionPoint } from '../types/home.types';
    defineProps<{ point: CollectionPoint }>();
</script>
