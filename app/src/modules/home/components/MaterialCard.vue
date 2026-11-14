<template>
    <article class="material-card">
        <div class="material-card__icon" :style="{ color: material.color, background: material.background }">
            <q-icon :name="material.icon" size="27px" />
        </div>
        <div><h3>{{ material.name }}</h3><p>{{ material.description }}</p></div>
        <q-icon name="arrow_forward" class="material-card__arrow" />
    </article>
</template>

<script setup lang="ts">
    import type { WasteMaterial } from '../types/home.types';
    defineProps<{ material: WasteMaterial }>();
</script>
