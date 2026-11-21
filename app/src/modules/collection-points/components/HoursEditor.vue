<template>
    <section>
        <h2 class="text-xl font-bold text-eco-900">Horários</h2>
        <p v-if="errors.opening_hours" role="alert" class="text-negative">{{ errors.opening_hours }}</p>

        <div v-for="day in 7" :key="day" class="mb-2 grid gap-2 rounded-xl border border-line p-3 sm:grid-cols-[1fr_1fr_1fr]">
            <q-checkbox
                :model-value="!!hours[day - 1]"
                :label="dayLabels[day - 1]"
                @update:model-value="toggle(day - 1, !!$event)"
            />
            <q-input
                v-if="hours[day - 1]"
                :model-value="hours[day - 1]?.opens_at"
                outlined
                type="time"
                label="Abre"
                :error="!!errors[`opening_hours.${indexOf(day - 1)}.opens_at`]"
                :error-message="errors[`opening_hours.${indexOf(day - 1)}.opens_at`]"
                @update:model-value="change(day - 1, 'opens_at', String($event))"
            />
            <q-input
                v-if="hours[day - 1]"
                :model-value="hours[day - 1]?.closes_at"
                outlined
                type="time"
                label="Fecha"
                :error="!!errors[`opening_hours.${indexOf(day - 1)}.closes_at`]"
                :error-message="errors[`opening_hours.${indexOf(day - 1)}.closes_at`]"
                @update:model-value="change(day - 1, 'closes_at', String($event))"
            />
            <p v-if="errors[`opening_hours.${indexOf(day - 1)}.opening_day`]" class="text-negative sm:col-span-3">
                {{ errors[`opening_hours.${indexOf(day - 1)}.opening_day`] }}
            </p>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { dayLabels, type OpeningHour } from '../types';

const props = defineProps<{
    modelValue: OpeningHour[];
    errors: Record<string, string>;
}>();
const emit = defineEmits<{ 'update:modelValue': [value: OpeningHour[]] }>();

const hours = computed(() => Object.fromEntries(props.modelValue.map(hour => [hour.opening_day, hour])));

function indexOf(day: number) {
    return props.modelValue.findIndex(hour => hour.opening_day === day);
}

function toggle(day: number, enabled: boolean) {
    const next = props.modelValue.filter(hour => hour.opening_day !== day);
    if (enabled) next.push({ opening_day: day, opens_at: '08:00', closes_at: '18:00' });
    emit('update:modelValue', next.sort((a, b) => a.opening_day - b.opening_day));
}

function change(day: number, field: 'opens_at' | 'closes_at', value: string) {
    emit('update:modelValue', props.modelValue.map(hour => hour.opening_day === day
        ? { ...hour, [field]: value } : hour));
}
</script>
