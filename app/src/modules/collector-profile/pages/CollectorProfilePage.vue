<template>
    <q-page class="bg-[linear-gradient(180deg,#f3faf6_0%,#fbfaf5_58%)] px-4 py-8 sm:px-6 sm:py-12">
        <main class="mx-auto w-full max-w-[960px]">
            <header class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <RouterLink
                        to="/area"
                        class="mb-4 inline-flex items-center gap-1 text-sm font-bold text-eco-700 no-underline hover:text-eco-900"
                    >
                        <q-icon name="arrow_back" />
                        Voltar para sua área
                    </RouterLink>
                    <span
                        class="block text-xs font-extrabold uppercase tracking-[0.14em] text-eco-600"
                    >
                        Perfil de coleta
                    </span>
                    <h1
                        class="mb-2 mt-2 text-[clamp(2rem,6vw,3rem)] font-extrabold leading-tight tracking-[-0.04em] text-eco-900"
                    >
                        Configure como você coleta
                    </h1>
                    <p class="m-0 max-w-2xl text-sm leading-6 text-muted sm:text-base">
                        Informe onde você atende e quais resíduos consegue receber. Você poderá
                        revisar tudo antes de salvar.
                    </p>
                </div>
            </header>

            <div
                v-if="loading"
                role="status"
                aria-live="polite"
                class="grid min-h-[320px] place-items-center rounded-3xl border border-line bg-white shadow-eco"
            >
                <div class="text-center">
                    <q-spinner
                        color="primary"
                        size="42px"
                    />
                    <p class="mb-0 mt-4 text-sm font-bold text-eco-900">
                        Carregando sua configuração…
                    </p>
                </div>
            </div>

            <q-form
                v-else-if="ready"
                class="grid gap-5"
                greedy
                @submit.prevent="submit"
            >
                <div
                    v-if="pageError"
                    role="alert"
                    aria-live="assertive"
                    class="flex items-start gap-3 rounded-2xl border border-[#efbaba] bg-[#fff3f3] p-4 text-sm leading-6 text-[#8f2929]"
                >
                    <q-icon
                        name="error_outline"
                        size="22px"
                        class="mt-0.5 shrink-0"
                    />
                    <div>
                        <strong class="block">
                            Não foi possível concluir a configuração
                        </strong>
                        <span>
                            {{ pageError }}
                        </span>
                    </div>
                </div>
                <div
                    v-if="successMessage"
                    role="status"
                    aria-live="polite"
                    class="flex items-center gap-3 rounded-2xl border border-[#c8ddcf] bg-[#f4faf6] p-4 text-sm text-eco-800"
                >
                    <q-icon
                        name="check_circle"
                        size="22px"
                    />
                    {{ successMessage }}
                </div>

                <section
                    class="rounded-3xl border border-line bg-white p-5 shadow-eco sm:p-7"
                    aria-labelledby="description-title"
                >
                    <div class="mb-5 flex items-start gap-3">
                        <span
                            class="grid size-11 shrink-0 place-items-center rounded-xl bg-eco-100 text-eco-700"
                        >
                            <q-icon
                                name="description"
                                size="23px"
                            />
                        </span>
                        <div>
                            <h2
                                id="description-title"
                                class="m-0 text-lg font-extrabold text-eco-900"
                            >
                                Descrição do serviço
                            </h2>
                            <p class="mb-0 mt-1 text-sm leading-5 text-muted">
                                Conte de forma clara como funciona sua coleta.
                            </p>
                        </div>
                    </div>
                    <q-input
                        v-model="form.service_description"
                        outlined
                        type="textarea"
                        autogrow
                        maxlength="500"
                        name="service_description"
                        label="Como você realiza as coletas? *"
                        :disable="submitting"
                        :error="Boolean(errors.service_description)"
                        :error-message="errors.service_description"
                        :rules="descriptionRules"
                        counter
                        class="w-full"
                    />
                    <div
                        v-if="form.service_description.trim()"
                        class="mt-3 rounded-xl bg-eco-50 p-4"
                    >
                        <span class="text-xs font-extrabold uppercase tracking-wider text-eco-700">
                            Prévia da descrição
                        </span>
                        <p
                            class="mb-0 mt-2 whitespace-pre-wrap break-words text-sm leading-6 text-ink"
                        >
                            {{ form.service_description.trim() }}
                        </p>
                    </div>
                </section>

                <section
                    class="rounded-3xl border border-line bg-white p-5 shadow-eco sm:p-7"
                    aria-labelledby="city-title"
                >
                    <div class="mb-5 flex items-start gap-3">
                        <span
                            class="grid size-11 shrink-0 place-items-center rounded-xl bg-eco-100 text-eco-700"
                        >
                            <q-icon
                                name="location_on"
                                size="23px"
                            />
                        </span>
                        <div>
                            <h2
                                id="city-title"
                                class="m-0 text-lg font-extrabold text-eco-900"
                            >
                                Cidade atendida
                            </h2>
                            <p class="mb-0 mt-1 text-sm leading-5 text-muted">
                                A cidade selecionada aparecerá aqui com destaque.
                            </p>
                        </div>
                    </div>
                    <CitySelect
                        name="cities"
                        v-model="form.cities"
                        multiple
                        label="Cidades atendidas"
                        :error="errors.cities"
                        :disable="submitting"
                    />
                    <p
                        class="mb-0 mt-2 text-xs"
                        :class="errors.cities ? 'text-negative' : 'text-muted'"
                    >
                        {{ errors.cities || citySummary }}
                    </p>
                </section>

                <section
                    class="rounded-3xl border border-line bg-white p-5 shadow-eco sm:p-7"
                    aria-labelledby="category-title"
                >
                    <div class="mb-5 flex items-start gap-3">
                        <span
                            class="grid size-11 shrink-0 place-items-center rounded-xl bg-eco-100 text-eco-700"
                        >
                            <q-icon
                                name="recycling"
                                size="23px"
                            />
                        </span>
                        <div>
                            <h2
                                id="category-title"
                                class="m-0 text-lg font-extrabold text-eco-900"
                            >
                                Resíduos aceitos
                            </h2>
                            <p class="mb-0 mt-1 text-sm leading-5 text-muted">
                                Selecione uma ou mais categorias.
                            </p>
                        </div>
                    </div>
                    <div
                        v-if="categories.length"
                        class="grid grid-cols-2 gap-3 sm:grid-cols-3"
                    >
                        <button
                            v-for="category in categories"
                            :key="category.id"
                            type="button"
                            :disabled="submitting"
                            :aria-pressed="isSelected(category.id)"
                            @click="toggleCategory(category.id)"
                            class="min-h-[64px] rounded-2xl border px-3 py-3 text-left text-sm font-bold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-eco-600 disabled:opacity-60"
                            :class="
                                isSelected(category.id)
                                    ? 'border-eco-600 bg-eco-100 text-eco-900 shadow-sm'
                                    : 'border-line bg-white text-ink hover:border-eco-200 hover:bg-eco-50'
                            "
                        >
                            <span class="flex items-center gap-2">
                                <q-icon
                                    :name="
                                        isSelected(category.id)
                                            ? 'check_circle'
                                            : 'radio_button_unchecked'
                                    "
                                    size="20px"
                                    class="shrink-0 text-eco-600"
                                />
                                {{ category.name }}
                            </span>
                        </button>
                    </div>
                    <div
                        v-else
                        class="rounded-2xl border border-dashed border-[#b9cec4] bg-[#f8fbf9] p-5 text-center text-sm text-muted"
                    >
                        Nenhuma categoria de resíduo foi retornada pela API.
                    </div>
                    <p
                        class="mb-0 mt-3 text-xs font-bold"
                        :class="errors.waste_category ? 'text-negative' : 'text-muted'"
                        aria-live="polite"
                    >
                        {{ errors.waste_category || selectionSummary }}
                    </p>
                </section>

                <section
                    class="rounded-3xl border border-[#d5e4dc] bg-eco-50 p-5 sm:flex sm:items-center sm:justify-between sm:gap-5 sm:p-6"
                >
                    <div class="mb-4 sm:mb-0">
                        <strong class="block text-base text-eco-900">
                            Revise antes de salvar
                        </strong>
                        <p class="mb-0 mt-1 text-sm leading-5 text-muted">
                            As cidades e categorias selecionadas substituirão sua configuração
                            atual.
                        </p>
                    </div>
                    <button
                        type="submit"
                        :disabled="submitting"
                        :aria-busy="submitting"
                        class="inline-flex min-h-[50px] w-full shrink-0 items-center justify-center gap-2 rounded-xl bg-eco-600 px-6 text-sm font-extrabold text-white disabled:opacity-60 sm:w-auto"
                    >
                        <q-spinner
                            v-if="submitting"
                            size="20px"
                            color="white"
                        />
                        <q-icon
                            v-else
                            name="save"
                            size="20px"
                        />
                        {{ submitting ? "Salvando…" : "Salvar configuração" }}
                    </button>
                </section>
            </q-form>

            <div v-if="!loading && !ready" role="alert" class="text-negative">
                {{ pageError }}
                <q-btn flat label="Tentar novamente" @click="load" />
            </div>
        </main>
    </q-page>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from "vue";
import axios from "axios";
import { collectorProfileService } from "../services/collector-profile.service";
import type { WasteCategoryOption } from "../types/collector-profile.types";
import CitySelect from "@/components/CitySelect.vue";
import { scrollToFirstError } from "@/helpers/pages/scrollToFirstError";
import { readFormError } from "@/modules/auth/utils/form-errors";

const loading = ref(true);
const submitting = ref(false);
const ready = ref(false);
const previous = { cities: [] as number[], categories: [] as number[] };
const fieldOrder = ["service_description", "cities", "waste_category"];
const pageError = ref("");
const successMessage = ref("");
const categories = ref<WasteCategoryOption[]>([]);
const form = reactive<{ service_description: string; cities: number[]; waste_category: number[] }>({
    service_description: "",
    cities: [],
    waste_category: [],
});
const errors = reactive({ service_description: "", cities: "", waste_category: "" });

const descriptionRules = [
    (value: string) => Boolean(value.trim()) || "Descreva o serviço de coleta.",
    (value: string) => value.length <= 500 || "Use no máximo 500 caracteres.",
];

const selectionSummary = computed(() =>
    form.waste_category.length
        ? `${form.waste_category.length} ${form.waste_category.length === 1 ? "categoria selecionada" : "categorias selecionadas"}.`
        : "Selecione pelo menos uma categoria.",
);

const citySummary = computed(() =>
    form.cities.length
        ? `${form.cities.length} cidade(s) selecionada(s).`
        : "Selecione pelo menos uma cidade.",
);

function isSelected(id: number) {
    return form.waste_category.includes(id);
}

function toggleCategory(id: number) {
    const unique = new Set(form.waste_category);
    unique.has(id) ? unique.delete(id) : unique.add(id);
    form.waste_category = [...unique];
    errors.waste_category = "";
}

async function submit() {
    if (submitting.value) {
        return;
    }
    errors.service_description = form.service_description.trim()
        ? ""
        : "Descreva o serviço de coleta.";
    errors.cities = form.cities.length ? "" : "Selecione pelo menos uma cidade atendida.";
    errors.waste_category = form.waste_category.length ? "" : "Selecione pelo menos uma categoria.";
    if (errors.service_description || errors.cities || errors.waste_category) {
        await scrollToFirstError(fieldOrder, errors);
        return;
    }
    submitting.value = true;
    pageError.value = "";
    successMessage.value = "";
    try {
        const cities = [...new Set(form.cities)];
        const categories = [...new Set(form.waste_category)];
        const cityChanges = [
            ...previous.cities.filter(id => !cities.includes(id)).map(city_id => ({ city_id, delete: true })),
            ...cities.filter(id => !previous.cities.includes(id)).map(city_id => ({ city_id, delete: false })),
        ];
        const categoryChanges = [
            ...previous.categories.filter(id => !categories.includes(id)).map(waste_category_id => ({ waste_category_id, delete: true })),
            ...categories.filter(id => !previous.categories.includes(id)).map(waste_category_id => ({ waste_category_id, delete: false })),
        ];
        await collectorProfileService.update({
            service_description: form.service_description.trim(),
            ...(cityChanges.length ? { cities: cityChanges } : {}),
            ...(categoryChanges.length ? { waste_categories: categoryChanges } : {}),
        });
        previous.cities = cities;
        previous.categories = categories;
        successMessage.value = "Configuração atualizada com sucesso.";
    } catch (error) {
        const parsed = readFormError(error, "Não foi possível atualizar o perfil de coleta.");
        errors.service_description = parsed.fields.service_description ?? "";
        errors.cities = parsed.fields.cities ?? parsed.fields["cities.0"] ?? "";
        errors.waste_category =
            parsed.fields.waste_categories ?? Object.entries(parsed.fields).find(([key]) => key.startsWith("waste_categories."))?.[1] ?? "";
        pageError.value = parsed.message;
        await scrollToFirstError(fieldOrder, errors);
    } finally {
        submitting.value = false;
    }
}

async function load() {
    loading.value = true;
    pageError.value = "";
    try {
        const [catalog, profile] = await Promise.all([
            collectorProfileService.getWasteCategories(),
            collectorProfileService.getProfile(),
        ]);
        categories.value = catalog;
        form.service_description = profile.service_description ?? "";
        previous.cities = profile.cities.map(city => city.city_id);
        previous.categories = profile.waste_categories.map(category => category.waste_category_id);
        form.cities = [...previous.cities];
        form.waste_category = [...previous.categories];
        ready.value = true;
    } catch (error) {
        const status = axios.isAxiosError(error) ? error.response?.status : undefined;
        const parsed = readFormError(error, "Não foi possível carregar as categorias de resíduos.");
        pageError.value =
            status === 403
                ? "Sua conta não possui permissão para consultar as categorias de resíduos."
                : parsed.message;
    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>
