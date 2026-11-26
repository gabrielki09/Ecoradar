<template>
    <q-page class="bg-cream px-4 py-8">
        <main class="mx-auto max-w-[800px]">
            <RouterLink
                to="/area/coletas/mine"
                class="text-eco-700"
            >
                ← Minhas solicitações
            </RouterLink>

            <h1 class="text-3xl font-extrabold text-eco-900">
                Nova solicitação de coleta
            </h1>

            <q-form
                class="grid grid-cols-1 gap-4 rounded-2xl border border-line bg-white p-6"
                @submit.prevent="submit"
            >
                <p
                    v-if="message"
                    role="alert"
                    class="text-negative"
                >
                    {{ message }}
                </p>

                <CitySelect
                    v-model="form.city_id"
                    name="city_id"
                    label="Cidade *"
                    :error="errors.city_id ?? ''"
                    :disable="sending"
                />
                <q-input
                    v-model="form.cep"
                    name="cep"
                    outlined
                    label="CEP *"
                    type="tel"
                    inputmode="numeric"
                    autocomplete="postal-code"
                    placeholder="00000-000"
                    mask="#####-###"
                    maxlength="9"
                    :error="!!errors.cep"
                    :error-message="errors.cep"
                    @update:model-value="handleAutocompleteCEP(String($event ?? ''))"
                />
                <q-input
                    v-model="form.neighborhood"
                    name="neighborhood"
                    outlined
                    label="Bairro *"
                    maxlength="255"
                    :error="!!errors.neighborhood"
                    :error-message="errors.neighborhood"
                />
                <q-input
                    v-model="form.address"
                    name="address"
                    outlined
                    label="Endereço *"
                    maxlength="255"
                    :error="!!errors.address"
                    :error-message="errors.address"
                />

                <h2 class="text-xl font-bold text-eco-900">
                    Itens
                </h2>
                <p
                    v-if="catalogError"
                    role="alert"
                    class="text-negative"
                >
                    {{ catalogError }}
                    <q-btn
                        flat
                        label="Tentar novamente"
                        @click="loadCategories"
                    />
                </p>
                <p v-else-if="catalogLoading">
                    Carregando categorias…
                </p>
                <div
                    v-for="(item, index) in form.items"
                    :key="item.key"
                    class="grid grid-cols-1 gap-3 rounded-xl border border-line p-4"
                >
                    <div class="flex flex-wrap justify-between gap-2">
                        <strong>
                            Item {{ index + 1 }}
                        </strong>
                        <q-btn
                            v-if="form.items.length > 1"
                            flat
                            color="negative"
                            icon="delete"
                            label="Remover"
                            @click="form.items.splice(index, 1)"
                        />
                    </div>
                    <q-select
                        v-model="item.waste_category_id"
                        :name="`items.${index}.waste_category_id`"
                        :disable="catalogLoading || !!catalogError || sending"
                        outlined
                        emit-value
                        map-options
                        label="Categoria *"
                        :options="categoryOptions"
                        :error="!!errors[`items.${index}.waste_category_id`]"
                        :error-message="errors[`items.${index}.waste_category_id`]"
                    />
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <q-input
                            v-model="item.approximate_quantity"
                            :name="`items.${index}.approximate_quantity`"
                            outlined
                            type="number"
                            min="0"
                            step="any"
                            label="Quantidade aproximada *"
                            :error="!!errors[`items.${index}.approximate_quantity`]"
                            :error-message="errors[`items.${index}.approximate_quantity`]"
                        />
                        <q-select
                            v-model="item.quantity_unit"
                            :name="`items.${index}.quantity_unit`"
                            :options="quantityUnitOptions"
                            outlined
                            emit-value
                            map-options
                            label="Unidade de medida *"
                            :disable="sending"
                            :error="!!errors[`items.${index}.quantity_unit`]"
                            :error-message="errors[`items.${index}.quantity_unit`]"
                        />
                    </div>
                    <q-input
                        v-model="item.description"
                        :name="`items.${index}.description`"
                        outlined
                        type="textarea"
                        label="Descrição (opcional)"
                        maxlength="500"
                        :error="!!errors[`items.${index}.description`]"
                        :error-message="errors[`items.${index}.description`]"
                    />
                </div>

                <q-btn
                    flat
                    color="primary"
                    label="Adicionar item"
                    icon="add"
                    @click="addItem"
                />
                <div class="flex flex-wrap gap-3">
                    <q-btn
                        type="submit"
                        color="primary"
                        label="Criar solicitação"
                        :loading="sending"
                        :disable="catalogLoading || !!catalogError"
                    />
                    <q-btn
                        flat
                        label="Cancelar"
                        to="/area/coletas/mine"
                        :disable="sending"
                    />
                </div>
            </q-form>
        </main>
    </q-page>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from "vue";

import { useRouter } from "vue-router";
import { useQuasar } from "quasar";
import { scrollToFirstError } from "@/helpers/pages/scrollToFirstError";
import CitySelect from "@/components/CitySelect.vue";
import { collectorProfileService } from "@/modules/collector-profile/services/collector-profile.service";
import { readFormError } from "@/modules/auth/utils/form-errors";
import { useCepAutocomplete } from "@/composables/useCepAutocomplete";
import { cepDigits } from "@/utils/cep";
import { collectionService } from "../service";
import { useCollectionStore } from "../store";
import { quantityUnitOptions, type QuantityUnit } from "../types";

const router = useRouter();
const $q = useQuasar();
const store = useCollectionStore();
let nextKey = 1;
function newItem() {
    return {
        key: nextKey++,
        waste_category_id: null as number | null,
        approximate_quantity: "",
        quantity_unit: null as QuantityUnit | null,
        description: "",
    };
}

const form = reactive({
    city_id: null as number | null,
    cep: "",
    neighborhood: "",
    address: "",
    items: [newItem()],
});
const handleAutocompleteCEP = useCepAutocomplete(form);
const errors = reactive<Record<string, string>>({});
const message = ref("");
const sending = ref(false);
const catalogLoading = ref(false);
const catalogError = ref("");
const categoryOptions = ref<{ label: string; value: number }[]>([]);

function addItem() {
    form.items.push(newItem());
}

async function loadCategories() {
    catalogLoading.value = true;
    catalogError.value = "";
    try {
        categoryOptions.value = (await collectorProfileService.getAllWasteCategories()).map(
            (c) => ({ value: c.id, label: c.name }),
        );
    } catch (cause) {
        catalogError.value = readFormError(
            cause,
            "Não foi possível carregar as categorias.",
        ).message;
    } finally {
        catalogLoading.value = false;
    }
}

function validate() {
    for (const key of Object.keys(errors)) delete errors[key];
    if (!form.city_id) {
        errors.city_id = "Selecione uma cidade.";
    }
    if (!/^\d{8}$/.test(cepDigits(form.cep))) {
        errors.cep = "Informe 8 dígitos.";
    }
    if (!form.neighborhood.trim()) {
        errors.neighborhood = "Informe o bairro.";
    }
    if (!form.address.trim()) {
        errors.address = "Informe o endereço.";
    }
    const used = new Set<number>();
    form.items.forEach((item, index) => {
        if (!item.waste_category_id)
            errors[`items.${index}.waste_category_id`] = "Selecione uma categoria.";
        else if (used.has(item.waste_category_id))
            errors[`items.${index}.waste_category_id`] = "Categoria repetida.";
        else used.add(item.waste_category_id);
        if (!item.approximate_quantity || Number(item.approximate_quantity) <= 0)
            errors[`items.${index}.approximate_quantity`] =
                "Informe uma quantidade maior que zero.";
        if (!item.quantity_unit)
            errors[`items.${index}.quantity_unit`] = "Selecione a unidade de medida do item.";
    });
    return !Object.keys(errors).length;
}

async function submit() {
    if (sending.value || catalogLoading.value || catalogError.value) return;
    message.value = "";
    if (!validate() || !form.city_id) {
        await scrollToFirstError(Object.keys(errors), errors);
        return;
    }
    sending.value = true;
    try {
        const result = await collectionService.create({
            city_id: form.city_id,
            cep: cepDigits(form.cep),
            neighborhood: form.neighborhood.trim(),
            address: form.address.trim(),
            items: form.items.map((item) => ({
                waste_category_id: item.waste_category_id!,
                approximate_quantity: Number(item.approximate_quantity),
                quantity_unit: item.quantity_unit!,
                ...(item.description.trim() ? { description: item.description.trim() } : {}),
            })),
        });
        store.invalidate();
        $q.notify({ type: "positive", message: "Solicitação criada." });
        await router.replace(`/area/coletas/detalhe/${result.id}?origem=mine`);
    } catch (cause) {
        const parsed = readFormError(cause, "Não foi possível criar a solicitação.");
        Object.assign(errors, parsed.fields);
        message.value = parsed.message;
        await scrollToFirstError(Object.keys(errors), errors);
    } finally {
        sending.value = false;
    }
}

onMounted(loadCategories);
</script>
