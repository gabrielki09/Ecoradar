<template>
    <article class="point-card" :class="{ 'point-card--selected': selected }">
        <div class="point-card__top">
            <div class="point-card__icon">
                <q-icon
                name="place"
                    size="26px"
                />
            </div>
        </div>
        <h3>
            {{ point.name }}
        </h3>
        <p class="point-card__address">
            <q-icon name="location_on" />
            {{ point.address }}, {{ point.neighborhood }} · CEP {{ formatCep(point.cep) }}
        </p>
        <div v-if="alwaysExpanded || detailsOpen" class="point-card__details">
        <p v-if="point.opening_hours.length" class="point-card__schedule">
            Horários: {{ hours }}
        </p>
        <div v-if="point.categories.length" class="point-card__tags">
            <span
                v-for="category in point.categories"
                :key="category.id"
            >
                {{ category.name }}
            </span>
        </div>
        <p v-if="point.guidelines" class="point-card__guidelines">{{ point.guidelines }}</p>
        </div>
        <q-btn
            v-if="!alwaysExpanded"
            flat
            no-caps
            :label="detailsOpen ? 'Ocultar detalhes' : 'Ver detalhes'"
            :icon-right="detailsOpen ? 'expand_less' : 'expand_more'"
            :aria-expanded="detailsOpen"
            class="point-card__button"
            @click="toggleDetails"
        />
    </article>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { formatCep } from '@/utils/cep';
import { dayLabels } from '@/modules/collection-points/types';
import type { PublicPoint } from '../types/public-point.types';

const props = defineProps<{ point: PublicPoint; selected?: boolean; alwaysExpanded?: boolean }>();
const emit = defineEmits<{ select: [] }>();
const detailsOpen = ref(false);
function toggleDetails() {
    detailsOpen.value = !detailsOpen.value;
    if (detailsOpen.value) emit('select');
}
const hours = computed(() => props.point.opening_hours.map(hour =>
    `${dayLabels[hour.opening_day] ?? 'Dia'} ${hour.opens_at.slice(0, 5)}–${hour.closes_at.slice(0, 5)}`,
).join('; '));
</script>
