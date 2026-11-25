<template>
    <q-page class="bg-cream px-4 py-8">
        <main class="mx-auto max-w-[850px]">
            <RouterLink
                :to="`/area/coletas/${origin}`"
                class="text-eco-700"
            >
                ← Voltar
            </RouterLink>

            <q-select
                v-if="store.companies.length > 1"
                :model-value="store.selectedCompanyId"
                :options="store.companies"
                option-label="company_name"
                option-value="id"
                label="Empresa da coleta"
                class="my-4 max-w-sm"
                outlined
                emit-value
                map-options
                @update:model-value="store.selectCompany"
            />

            <div
                v-if="loading"
                class="py-12 text-center"
                role="status"
            >
                <q-spinner
                    size="36px"
                    color="primary"
                />
                <p>Carregando solicitação…</p>
            </div>
            <div
                v-else-if="error"
                role="alert"
                class="mt-5 rounded-xl bg-red-50 p-5 text-negative"
            >
                {{ error }}
                <q-btn
                    flat
                    label="Tentar novamente"
                    @click="load"
                />
            </div>

            <template v-else-if="record">
                <div class="mt-5 flex flex-wrap items-center justify-between gap-2">
                    <h1 class="text-3xl font-extrabold text-eco-900">
                        Solicitação #{{ record.id }}
                    </h1>
                    <q-badge color="primary">
                        {{ statusLabels[record.status] ?? record.status }}
                    </q-badge>
                </div>

                <section class="rounded-2xl border border-line bg-white p-6">
                    <h2 class="text-lg font-bold">
                        Localização e itens
                    </h2>
                    <p>
                        {{ record.neighborhood }} · {{ record.city?.municipio_ibge ?? record.city?.municipio_tom }}{{ record.city?.uf ? ' / ' + record.city.uf : '' }}
                        <span v-if="record.address && record.cep">
                            · {{ record.address }} · CEP {{ formatCep(record.cep) }}
                        </span>
                    </p>
                    <ul v-if="record.items?.length">
                        <li
                            v-for="item in record.items"
                            :key="item.id ?? item.waste_category_id"
                        >
                            {{ item.waste_category?.name ?? `Categoria #${item.waste_category_id}` }} ·
                            {{ item.approximate_quantity }}<span v-if="item.description"> · {{ item.description }}</span>
                        </li>
                    </ul>
                    <p
                        v-else
                        class="text-muted"
                    >
                        Os itens não foram retornados pela API.
                    </p>
                </section>

                <section class="mt-4 rounded-2xl border border-line bg-white p-6">
                    <h2 class="text-lg font-bold">
                        Acompanhamento
                    </h2>
                    <div
                        v-for="stage in dates"
                        :key="stage.label"
                        class="flex flex-wrap justify-between gap-2 border-b border-line py-2"
                    >
                        <span>
                            {{ stage.label }}
                        </span>
                        <span>
                            {{ stage.value ? formatDate(stage.value) : "Pendente" }}
                        </span>
                    </div>
                </section>
                <div
                    v-if="action"
                    class="mt-5"
                >
                    <q-btn
                        color="primary"
                        :label="action.label"
                        :loading="sending"
                        :disable="sending"
                        @click="confirmAction"
                    />
                </div>
                <q-banner
                    v-else-if="isProvider && !companyReady"
                    class="mt-5 rounded-xl bg-amber-100"
                >
                    As ações da empresa estão indisponíveis porque nenhuma empresa vinculada foi
                    retornada para sua conta.
                </q-banner>
                <q-banner
                    v-else-if="record.acceptance_block_reason"
                    class="mt-5 rounded-xl bg-eco-100"
                >
                    {{ record.acceptance_block_reason }}
                </q-banner>
            </template>
        </main>
    </q-page>
</template>

<script setup lang="ts">
import { computed, ref, watch, onBeforeUnmount } from "vue";

import { useRoute } from "vue-router";
import { useQuasar } from "quasar";
import axios from "axios";
import { useAuthStore } from "@/modules/auth/stores/auth.store";
import { readFormError } from "@/modules/auth/utils/form-errors";
import { formatCep } from "@/utils/cep";
import { collectionService } from "../service";
import { useCollectionStore } from "../store";
import { statusLabels, type Area, type CollectionRequest } from "../types";

const route = useRoute();
const $q = useQuasar();
const auth = useAuthStore();
const store = useCollectionStore();
const record = ref<CollectionRequest | null>(null);
const loading = ref(false);
const sending = ref(false);
const error = ref("");
let sequence = 0;

const origin = computed<Area>(() =>
    auth.canRequest
        ? "mine"
        : ["all", "available", "for-me"].includes(String(route.query.origem))
            ? (route.query.origem as Area)
            : "all",
);
const id = computed(() => Number(route.params.id));
const isProvider = computed(() => auth.canProvide);
const companyReady = computed(
    () => auth.user?.profile_type !== "company" || !!store.selectedCompanyId,
);

const own = computed(() => record.value?.requester_user_id === auth.user?.id);
const action = computed(() => {
    if (!record.value) {
        return null;
    }
    if (auth.canRequest && own.value && record.value.status === "open")
        return { type: "cancel" as const, label: "Cancelar solicitação" };
    if (!isProvider.value || !companyReady.value) {
        return null;
    }
    if (
        record.value.acceptance_block_reason === null &&
        record.value.status === "open"
    )
        return { type: "accept" as const, label: "Aceitar pedido" };
    if (record.value.assigned_to_provider && record.value.status === "accepted")
        return { type: "collected" as const, label: "Marcar como coletado" };
    if (record.value.assigned_to_provider && record.value.status === "collected")
        return { type: "complete" as const, label: "Concluir coleta" };
    return null;
});

const dates = computed(() =>
    record.value
        ? [
              { label: "Criada", value: record.value.created_at },
              { label: "Aceita", value: record.value.acceptance_date },
              { label: "Coletada", value: record.value.withdrawal_date },
              { label: "Concluída", value: record.value.completion_date },
              { label: "Cancelada", value: record.value.canceled_date },
          ].filter(
              (row) =>
                  row.label === "Criada" ||
                  row.value ||
                  (row.label === "Cancelada"
                      ? record.value?.status === "cancelled"
                      : record.value?.status !== "cancelled"),
          )
        : [],
);

function formatDate(value: string) {
    return new Date(value).toLocaleString("pt-BR");
}

async function load() {
    const current = ++sequence;
    loading.value = true;
    error.value = "";
    record.value = null;
    try {
        const result = await collectionService.detail(id.value, store.selectedCompanyId ?? undefined);
        if (current !== sequence) {
            return;
        }
        if (auth.canRequest && result.requester_user_id !== auth.user?.id) {
            error.value = "Você não pode consultar esta solicitação.";
            return;
        }
        record.value = result;
    } catch (cause) {
        if (current === sequence)
            error.value = readFormError(cause, "Não foi possível abrir a solicitação.").message;
    } finally {
        if (current === sequence) {
            loading.value = false;
        }
    }
}

function confirmAction() {
    if (!action.value || sending.value) {
        return;
    }
    const chosen = action.value;
    if (chosen.type === "cancel") {
        $q.dialog({
            title: "Cancelar solicitação?",
            message: "Informe o motivo do cancelamento (até 500 caracteres).",
            prompt: {
                model: "",
                type: "textarea",
                isValid: (value) => !!value.trim() && value.length <= 500,
            },
            cancel: true,
            persistent: true,
        }).onOk((reason: string) => void execute(chosen.type, reason.trim()));
    } else {
        $q.dialog({
            title: "Confirmar alteração",
            message: `${chosen.label}?`,
            cancel: true,
            persistent: true,
        }).onOk(() => void execute(chosen.type));
    }
}

async function execute(type: "accept" | "collected" | "complete" | "cancel", reason?: string) {
    if (!record.value || sending.value || action.value?.type !== type) {
        return;
    }
    sending.value = true;
    const current = sequence;
    const company =
        auth.user?.profile_type === "company" ? (store.selectedCompanyId ?? undefined) : undefined;
    try {
        const result = await collectionService.transition(type, record.value.id, company, reason);
        if (current !== sequence) {
            return;
        }
        record.value = result;
        store.invalidate();
        $q.notify({ type: "positive", message: "Solicitação atualizada." });
        await load();
    } catch (cause) {
        if (current !== sequence) {
            return;
        }
        const conflict = axios.isAxiosError(cause) && cause.response?.status === 409;
        $q.notify({
            type: "negative",
            message: conflict
                ? "O pedido mudou de estado ou não está mais disponível. Atualizando os dados."
                : readFormError(cause, "Não foi possível atualizar a solicitação.").message,
        });
        if (conflict) {
            store.invalidate();
            await load();
        }
    } finally {
        sending.value = false;
    }
}

watch([id, () => store.selectedCompanyId], () => void load());

onBeforeUnmount(() => {
    sequence++;
});
void load();
</script>
