<template>
    <q-page class="bg-cream px-4 py-8">
        <main class="mx-auto max-w-[1120px]">
            <h1 class="text-3xl font-extrabold text-eco-900">
                {{ areaLabels[area] }}
            </h1>

            <nav
                class="mb-6 flex flex-wrap gap-2"
                aria-label="Áreas de coleta"
            >
                <RouterLink
                    v-for="entry in links"
                    :key="entry.area"
                    :to="`/area/coletas/${entry.area}`"
                    class="rounded-xl border border-line bg-white px-4 py-2 text-eco-900 no-underline"
                    :aria-current="area === entry.area ? 'page' : undefined"
                >
                    {{ entry.label }}
                </RouterLink>
                <RouterLink
                    v-if="auth.canRequest"
                    to="/area/coletas/nova"
                    class="rounded-xl bg-eco-600 px-4 py-2 text-white no-underline"
                >
                    Nova solicitação
                </RouterLink>
            </nav>

            <q-banner
                v-if="companyBlocked"
                class="mb-4 rounded-xl bg-amber-100 text-amber-950"
            >
                Nenhuma empresa vinculada foi retornada para sua conta. Aplicáveis e Minhas coletas
                ficam indisponíveis até que o vínculo esteja disponível.
            </q-banner>

            <div
                v-if="!companyBlocked"
                class="mb-5 flex flex-wrap items-end gap-3"
            >
                <q-select
                    v-if="area === 'all' || area === 'mine' || area === 'for-me'"
                    v-model="status"
                    outlined
                    dense
                    label="Status"
                    :options="statusOptions"
                    emit-value
                    map-options
                    clearable
                    class="w-56"
                />
                <q-btn
                    flat
                    color="primary"
                    label="Atualizar"
                    icon="refresh"
                    @click="load"
                />
            </div>

            <div
                v-if="loading"
                role="status"
                class="py-12 text-center"
            >
                <q-spinner
                    color="primary"
                    size="36px"
                />
                <p>
                    Carregando solicitações…
                </p>
            </div>
            <div
                v-else-if="error"
                role="alert"
                class="rounded-xl bg-red-50 p-5 text-negative"
            >
                {{ error }}
                <q-btn
                    flat
                    color="negative"
                    label="Tentar novamente"
                    @click="load"
                />
            </div>
            <div
                v-else-if="!companyBlocked && !records.length"
                class="rounded-xl border border-line bg-white p-8 text-center"
            >
                Nenhuma solicitação encontrada.
            </div>
            <div
                v-else
                class="grid gap-3"
            >
                <RouterLink
                    v-for="record in records"
                    :key="record.id"
                    :to="`/area/coletas/detalhe/${record.id}?origem=${area}`"
                    class="rounded-2xl border border-line bg-white p-5 text-inherit no-underline shadow-sm"
                >
                    <div class="flex justify-between gap-2">
                        <strong class="text-eco-900">
                            Solicitação #{{ record.id }}
                        </strong>
                        <q-badge color="primary">
                            {{ statusLabels[record.status] ?? record.status }}
                        </q-badge>
                    </div>
                    <p class="mb-0 mt-2 text-sm">
                        {{ record.address }}, {{ record.neighborhood }} · CEP {{ record.cep }}
                    </p>
                    <small
                        v-if="record.created_at"
                        class="text-muted"
                    >
                        Criada em {{ formatDate(record.created_at) }}
                    </small>
                </RouterLink>
            </div>
            <div
                v-if="!companyBlocked && !loading && !error && lastPage > 1"
                class="mt-6 flex justify-center"
            >
                <q-pagination
                    v-model="page"
                    :max="lastPage"
                    direction-links
                />
            </div>
        </main>
    </q-page>
</template>

<script setup lang="ts">
import { computed, ref, watch, onBeforeUnmount } from "vue";

import { useRoute } from "vue-router";
import { useAuthStore } from "@/modules/auth/stores/auth.store";
import { readFormError } from "@/modules/auth/utils/form-errors";
import { useCollectionStore } from "../store";
import { collectionService } from "../service";
import {
    areaLabels,
    statusLabels,
    type Area,
    type CollectionRequest,
    type CollectionStatus,
} from "../types";

const route = useRoute();
const auth = useAuthStore();
const store = useCollectionStore();
const statusOptions = Object.entries(statusLabels).map(([value, label]) => ({ value, label }));
const status = ref<CollectionStatus | null>(null);
const page = ref(1);
const lastPage = ref(1);
const records = ref<CollectionRequest[]>([]);
const loading = ref(false);
const error = ref("");
let sequence = 0;

const area = computed<Area>(
    () =>
        (["all", "available", "for-me", "mine"].includes(String(route.params.area))
            ? String(route.params.area)
            : "mine") as Area,
);

const companyBlocked = computed(
    () =>
        auth.user?.profile_type === "company" &&
        (area.value === "available" || area.value === "for-me") &&
        !store.selectedCompanyId,
);

const links = computed(() =>
    Object.entries(areaLabels)
        .filter(([key]) => auth.canProvide || (key === "mine" && auth.canRequest))
        .map(([key, label]) => ({ area: key as Area, label })),
);

function formatDate(value: string) {
    return new Date(value).toLocaleString("pt-BR");
}

async function load() {
    const current = ++sequence;
    records.value = [];
    error.value = "";
    if (area.value === "available") {
        store.availableIds = [];
    }
    if (area.value === "for-me") {
        store.assignedIds = [];
    }
    if (companyBlocked.value) {
        loading.value = false;
        return;
    }
    loading.value = true;
    try {
        const result = await collectionService.list(
            area.value,
            page.value,
            15,
            status.value ?? "",
            store.selectedCompanyId ?? undefined,
        );
        if (current !== sequence) {
            return;
        }
        records.value = result.data;
        lastPage.value = result.last_page;
        if (area.value === "available") {
            store.availableIds = result.data.map((item) => item.id);
        }
        if (area.value === "for-me") {
            store.assignedIds = result.data.map((item) => item.id);
        }
    } catch (cause) {
        if (current === sequence)
            error.value = readFormError(
                cause,
                "Não foi possível carregar as solicitações.",
            ).message;
    } finally {
        if (current === sequence) {
            loading.value = false;
        }
    }
}

watch(area, () => {
    page.value = 1;
    if (status.value !== null) {
        status.value = null;
    }
    else void load();
});

watch(
    [
        status,
        () =>
            area.value === "available" || area.value === "for-me" ? store.selectedCompanyId : null,
    ],
    () => {
        page.value = 1;
        void load();
    },
);

watch([page, () => store.revision], () => void load());

onBeforeUnmount(() => {
    sequence++;
});
void load();
</script>
