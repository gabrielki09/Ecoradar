<template>
    <article class="grid min-h-[105px] grid-cols-[auto_1fr_auto] items-center gap-[15px] rounded-2xl border border-line bg-white p-[19px] transition-[transform,box-shadow,border-color] duration-200 hover:-translate-y-[3px] hover:border-[#b6d6c5] hover:shadow-[0_12px_30px_rgba(22,86,62,.08)]">
        <div class="grid size-[53px] place-items-center rounded-[14px]" :style="{ color: material.color, background: material.background }">
            <q-icon :name="material.icon" size="27px" />
        </div>
        <div><h3 class="mb-1 mt-0 text-[15px] text-eco-900">{{ material.name }}</h3><p class="m-0 text-xs text-muted">{{ material.description }}</p></div>
        <q-icon name="arrow_forward" class="text-[#97aaa2]" />
    </article>
</template>

<script setup lang="ts">
    import type { WasteMaterial } from '../types/home.types';
    defineProps<{ material: WasteMaterial }>();
</script>
