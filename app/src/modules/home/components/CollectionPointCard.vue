<template>
    <article class="rounded-[18px] border border-line p-[22px] transition-[transform,box-shadow] duration-200 hover:-translate-y-1 hover:shadow-eco">
        <div class="flex items-center justify-between">
            <div class="grid size-[50px] place-items-center rounded-[14px] bg-eco-100 text-eco-600"><q-icon :name="point.icon" size="26px" /></div>
            <span class="text-[11px] text-muted"><q-icon name="near_me" /> {{ point.distance }}</span>
        </div>
        <h3 class="mb-[7px] mt-[18px] text-[17px] text-eco-900">{{ point.name }}</h3>
        <p class="m-0 flex items-center gap-[5px] text-xs text-muted"><q-icon name="location_on" /> {{ point.address }}</p>
        <p class="my-3 flex items-center gap-[7px] text-[11px] font-semibold text-eco-600"><span class="size-[7px] rounded-full bg-[#35b879]"></span>{{ point.schedule }}</p>
        <div class="flex min-h-7 flex-wrap gap-1.5"><span v-for="material in point.materials" :key="material" class="rounded-[7px] bg-[#f1f6f3] px-2 py-[5px] text-[10px] text-[#60756d]">{{ material }}</span></div>
        <q-btn flat no-caps label="Ver detalhes" icon-right="arrow_forward" class="mt-[18px] w-full rounded-none border-t border-[#edf2ef] font-bold text-eco-600" />
    </article>
</template>

<script setup lang="ts">
    import type { CollectionPoint } from '../types/home.types';
    defineProps<{ point: CollectionPoint }>();
</script>
