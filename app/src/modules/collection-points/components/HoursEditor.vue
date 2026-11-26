<template>
    <section data-error-field="opening_hours" tabindex="-1">
        <h2 class="text-xl font-bold text-eco-900">Horários</h2>
        <p v-if="errors.opening_hours" role="alert" class="text-negative">{{ errors.opening_hours }}</p>
        <p class="text-sm text-eco-700">
            Configure um dia e replique o horário para todos. Os horários dos outros dias serão substituídos.
        </p>

        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center">
            <q-select
                :model-value="selectedSourceDay"
                :options="sourceDayOptions"
                label="Copiar horário de"
                outlined
                dense
                emit-value
                map-options
                class="sm:min-w-52"
                :disable="!sourceDayOptions.length"
                @update:model-value="sourceDay = Number($event)"
            />
            <q-btn
                label="Replicar para todos os dias"
                type="button"
                color="primary"
                outline
                no-caps
                :disable="selectedSourceDay === null"
                @click="replicateHours"
            />
        </div>

        <div
            v-for="day in 7"
            :key="day"
            class="mb-2 grid gap-2 rounded-xl border border-line p-3 sm:grid-cols-[1fr_1fr_1fr]"
        >
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
                :name="`opening_hours.${indexOf(day - 1)}.opens_at`"
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
                :name="`opening_hours.${indexOf(day - 1)}.closes_at`"
                :error="!!errors[`opening_hours.${indexOf(day - 1)}.closes_at`]"
                :error-message="errors[`opening_hours.${indexOf(day - 1)}.closes_at`]"
                @update:model-value="change(day - 1, 'closes_at', String($event))"
            />
            <p
                v-if="errors[`opening_hours.${indexOf(day - 1)}.opening_day`]"
                class="text-negative sm:col-span-3"
            >
                {{ errors[`opening_hours.${indexOf(day - 1)}.opening_day`] }}
            </p>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';

import { dayLabels, type OpeningHour } from '../types';

const props = defineProps<{
    modelValue: OpeningHour[];
    errors: Record<string, string>;
}>();
const emit = defineEmits<{ 'update:modelValue': [value: OpeningHour[]] }>();

const sourceDay = ref<number | null>(null);
const hours = computed(() => Object.fromEntries(props.modelValue.map(hour => [hour.opening_day, hour])));
const sourceDayOptions = computed(() => props.modelValue.map(hour => ({
    label: dayLabels[hour.opening_day],
    value: hour.opening_day,
})));
const selectedSourceDay = computed(() => {
    if (sourceDay.value !== null && props.modelValue.some(hour => hour.opening_day === sourceDay.value)) {
        return sourceDay.value;
    }

    return props.modelValue[0]?.opening_day ?? null;
});

function indexOf(day: number) {
    return props.modelValue.findIndex(hour => hour.opening_day === day);
}

function toggle(day: number, enabled: boolean) {
    const next = props.modelValue.filter(hour => hour.opening_day !== day);
    if (enabled) {
        next.push({ opening_day: day, opens_at: '08:00', closes_at: '18:00' });
    }

    emit('update:modelValue', next.sort((a, b) => a.opening_day - b.opening_day));
}

function change(day: number, field: 'opens_at' | 'closes_at', value: string) {
    emit('update:modelValue', props.modelValue.map(hour => {
        if (hour.opening_day === day) {
            return { ...hour, [field]: value };
        }

        return hour;
    }));
}

function replicateHours() {
    const source = props.modelValue.find(hour => hour.opening_day === selectedSourceDay.value);
    if (!source) {
        return;
    }

    emit('update:modelValue', dayLabels.map((_, opening_day) => ({
        opening_day,
        opens_at: source.opens_at,
        closes_at: source.closes_at,
    })));
}
</script>
