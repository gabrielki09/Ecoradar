<template>
    <q-select
        :model-value="modelValue"
        :multiple="multiple"
        :use-chips="multiple"
        :label="label"
        outlined
        use-input
        :options="options"
        option-value="id"
        :option-label="cityLabel"
        emit-value
        map-options
        :display-value="multiple ? undefined : displayValue"
        :loading="loading"
        :error="Boolean(error)"
        :error-message="error"
        :disable="disable"
        @update:model-value="(value) => $emit('update:modelValue', value)"
        @popup-show="open"
        @filter="filterOptions"
        @virtual-scroll="onScroll"
    >
        <template #before-options>
            <div class="q-px-md q-pt-sm">
                <q-select
                    v-model="searchField"
                    dense
                    outlined
                    label="Buscar por"
                    :options="searchFields"
                    emit-value
                    map-options
                    @update:model-value="restart"
                />
            </div>
        </template>
        <template #after-options>
            <div class="q-pa-sm text-center text-caption">
                <q-spinner
                    v-if="loading"
                    size="20px"
                />
                <q-btn
                    v-else-if="loadError"
                    flat
                    color="negative"
                    label="Erro ao carregar. Tentar novamente"
                    @click.stop="loadNext"
                />
                <span v-else-if="!options.length">
                    Nenhuma cidade encontrada.
                </span>
                <span v-else-if="ended">
                    Fim da lista.
                </span>
            </div>
        </template>
    </q-select>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, reactive, ref, watch } from "vue";
import { api } from "@/boot/axios";
import type { ApiResponse } from "@/types/api/api.types";
import type {
    CityIndexData,
    CityOption,
} from "@/modules/collector-profile/types/collector-profile.types";

const props = withDefaults(
    defineProps<{
        modelValue: number | number[] | null;
        multiple?: boolean;
        label?: string;
        error?: string;
        disable?: boolean;
    }>(),
    { label: "Cidade" },
);
defineEmits<{ "update:modelValue": [value: number | number[] | null] }>();

const options = ref<CityOption[]>([]);
const known = reactive(new Map<number, CityOption>());
const loading = ref(false);
const loadError = ref(false);
const page = ref(1);
const lastPage = ref<number | null>(null);
const search = ref("");
const searchField = ref("municipio_tom");
const searchFields = [
    { label: "Nome TOM", value: "municipio_tom" },
    { label: "Nome IBGE", value: "municipio_ibge" },
    { label: "UF", value: "uf" },
];
let generation = 0;
let timer: ReturnType<typeof setTimeout> | undefined;

const ended = computed(() => lastPage.value !== null && page.value > lastPage.value);
const displayValue = computed(() => {
    const ids = Array.isArray(props.modelValue)
        ? props.modelValue
        : props.modelValue == null
          ? []
          : [props.modelValue];
    return ids
        .map((id) => (known.has(id) ? cityLabel(known.get(id)!) : `Cidade #${id}`))
        .join(", ");
});

function cityLabel(city: CityOption | number): string {
    if (typeof city === "number") {
        const option = known.get(city);
        return option ? cityLabel(option) : `Cidade #${city}`;
    }
    return `${city.municipio_tom || city.municipio_ibge || "Cidade"} / ${city.uf}`;
}

function restart() {
    generation++;
    options.value = [];
    page.value = 1;
    lastPage.value = null;
    loadError.value = false;
    loading.value = false;
    void loadNext();
}

function open() {
    if (!options.value.length && !loading.value && !loadError.value) {
        void loadNext();
    }
}

function filterOptions(value: string, update: (fn: () => void) => void) {
    update(() => {});
    clearTimeout(timer);
    timer = setTimeout(() => {
        if (search.value !== value) {
            search.value = value;
            restart();
        }
    }, 350);
}

function onScroll(details: { to: number }) {
    if (details.to >= options.value.length - 1) {
        void loadNext();
    }
}

async function loadNext() {
    if (loading.value || ended.value) {
        return;
    }
    const current = generation;
    const requestedPage = page.value;
    loading.value = true;
    loadError.value = false;
    try {
        const params: Record<string, unknown> = { page: requestedPage, per_page: 30 };
        if (search.value.trim())
            params.filtersV1 = [
                { field: searchField.value, filterType: "ILIKE", filterValue: search.value.trim() },
            ];
        const response = await api.get<ApiResponse<CityIndexData>>("/general/cities", { params });
        if (current !== generation) {
            return;
        }
        const paginator = response.data.data.cities;
        for (const city of paginator.data) {
            known.set(city.id, city);
            if (!options.value.some((option) => option.id === city.id)) {
                options.value.push(city);
            }
        }
        lastPage.value = paginator.last_page;
        page.value = requestedPage + 1;
    } catch {
        if (current === generation) {
            loadError.value = true;
        }
    } finally {
        if (current === generation) {
            loading.value = false;
        }
    }
}

watch(() => props.modelValue, async value => {
    const ids = Array.isArray(value) ? value : value == null ? [] : [value];
    const missing = ids.filter(id => !known.has(id));
    if (!missing.length) return;

    try {
        const response = await api.get<ApiResponse<CityIndexData>>("/general/cities", {
            params: {
                per_page: 100,
                filtersV1: [{ field: "id", filterType: "IN", filterValue: missing }],
            },
        });
        response.data.data.cities.data.forEach(city => known.set(city.id, city));
    } catch {
        // O seletor poderá carregar os nomes durante a busca.
    }
}, { immediate: true });

onBeforeUnmount(() => {
    generation++;
    clearTimeout(timer);
});
</script>
