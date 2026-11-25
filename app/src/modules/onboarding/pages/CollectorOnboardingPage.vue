<template>
    <OnboardingShell
        title-id="collector-onboarding-title"
        icon="local_shipping"
        title="Configure seu perfil de coleta"
        description="Informe como e onde você realiza coletas para ativar seu perfil individual no EcoRadar."
    >
        <q-form
            class="grid gap-6"
            greedy
            @submit="completeOnboarding"
        >
            <div
                v-if="feedback"
                role="alert"
                aria-live="assertive"
                class="flex items-start gap-3 rounded-xl border border-[#efbaba] bg-[#fff3f3] p-4 text-sm leading-6 text-[#8f2929]"
            >
                <q-icon
                    name="error_outline"
                    size="21px"
                    class="mt-0.5 shrink-0"
                />
                <span>
                    {{ feedback }}
                </span>
            </div>

            <section>
                <h2 class="mb-1 text-base font-extrabold text-eco-900">
                    <q-icon
                        name="description"
                        class="mr-2 text-eco-600"
                    />
                    Descrição do serviço
                </h2>
                <p class="mb-3 mt-0 text-xs text-muted">
                    Explique de forma clara como funciona sua coleta.
                </p>
                <q-input
                    v-model="form.service_description"
                    name="service_description"
                    outlined
                    type="textarea"
                    autogrow
                    maxlength="500"
                    counter
                    label="Descrição do serviço *"
                    :disable="loading"
                    :error="Boolean(errors.service_description)"
                    :error-message="errors.service_description"
                    :rules="descriptionRules"
                />
                <div
                    v-if="form.service_description.trim()"
                    class="mt-2 rounded-xl bg-eco-50 p-4"
                >
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-eco-700">
                        Prévia
                    </span>
                    <p class="mb-0 mt-1 whitespace-pre-wrap break-words text-sm leading-6 text-ink">
                        {{ form.service_description.trim() }}
                    </p>
                </div>
            </section>

            <section>
                <h2 class="mb-1 text-base font-extrabold text-eco-900">
                    <q-icon
                        name="location_on"
                        class="mr-2 text-eco-600"
                    />
                    Cidade atendida
                </h2>
                <p class="mb-3 mt-0 text-xs text-muted">
                    Escolha uma ou mais cidades onde você realiza coletas.
                </p>
                <CitySelect
                    name="cities"
                    v-model="form.cities"
                    multiple
                    label="Cidades atendidas"
                    :error="errors.cities"
                    :disable="loading"
                />
                <p
                    class="mb-0 mt-2 text-xs"
                    :class="errors.cities ? 'text-negative' : 'text-muted'"
                >
                    {{ errors.cities || citySummary }}
                </p>
            </section>

            <section>
                <h2 class="mb-1 text-base font-extrabold text-eco-900">
                    <q-icon
                        name="recycling"
                        class="mr-2 text-eco-600"
                    />
                    Resíduos aceitos
                </h2>
                <p class="mb-3 mt-0 text-xs text-muted">
                    Selecione pelo menos uma categoria.
                </p>
                <div
                    v-if="catalogLoading"
                    class="grid min-h-24 place-items-center"
                    role="status"
                >
                    <q-spinner
                        color="primary"
                        size="30px"
                    />
                </div>
                <div
                    v-else-if="categories.length"
                    class="grid grid-cols-2 gap-3 sm:grid-cols-3"
                >
                    <button
                        v-for="category in categories"
                        :key="category.id"
                        type="button"
                        :disabled="loading"
                        :aria-pressed="selected(category.id)"
                        @click="toggleCategory(category.id)"
                        class="min-h-[60px] rounded-xl border px-3 py-3 text-left text-sm font-bold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-eco-600"
                        :class="
                            selected(category.id)
                                ? 'border-eco-600 bg-eco-100 text-eco-900'
                                : 'border-line bg-white text-ink hover:bg-eco-50'
                        "
                    >
                        <span class="flex items-center gap-2">
                            <q-icon
                                :name="
                                    selected(category.id)
                                        ? 'check_circle'
                                        : 'radio_button_unchecked'
                                "
                                size="19px"
                                class="shrink-0 text-eco-600"
                            />
                            {{ category.name }}
                        </span>
                    </button>
                </div>
                <div
                    v-else
                    class="rounded-xl border border-dashed border-line p-4 text-center text-sm text-muted"
                >
                    Nenhuma categoria disponível.
                </div>
                <p
                    class="mb-0 mt-2 text-xs"
                    :class="errors.waste_category ? 'text-negative' : 'text-muted'"
                >
                    {{ errors.waste_category || categorySummary }}
                </p>
            </section>

            <q-btn
                v-if="catalogError"
                flat
                color="primary"
                label="Tentar carregar categorias novamente"
                @click="loadCategories"
            />
            <button
                type="submit"
                :disabled="loading || catalogLoading || !!catalogError"
                :aria-busy="loading"
                class="inline-flex min-h-[52px] w-full items-center justify-center gap-2 rounded-xl bg-eco-600 px-5 text-[15px] font-extrabold text-white shadow-[0_10px_24px_rgba(22,128,91,.2)] transition hover:bg-eco-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-eco-600 disabled:cursor-not-allowed disabled:opacity-50"
            >
                <q-spinner
                    v-if="loading"
                    size="22px"
                    color="white"
                />
                <span>
                    {{ loading ? "Ativando perfil…" : "Ativar perfil de coletor" }}
                </span>
            </button>
        </q-form>
    </OnboardingShell>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from "vue";
import axios from "axios";

import { useRouter } from "vue-router";
import OnboardingShell from "../components/OnboardingShell.vue";
import { onboardingService } from "../services/onboarding.service";
import { useAuthStore } from "@/modules/auth/stores/auth.store";
import { scrollToFirstError } from "@/helpers/pages/scrollToFirstError";
import { readFormError } from "@/modules/auth/utils/form-errors";
import { collectorProfileService } from "@/modules/collector-profile/services/collector-profile.service";
import type { WasteCategoryOption } from "@/modules/collector-profile/types/collector-profile.types";
import CitySelect from "@/components/CitySelect.vue";

const authStore = useAuthStore();
const router = useRouter();
const loading = ref(false);
const catalogError = ref(false);
const fieldOrder = ["service_description", "cities", "waste_category"];
const catalogLoading = ref(true);
const feedback = ref("");
const categories = ref<WasteCategoryOption[]>([]);
const form = reactive({
    service_description: "",
    cities: [] as number[],
    waste_category: [] as number[],
});
const errors = reactive({ service_description: "", cities: "", waste_category: "" });
const descriptionRules = [
    (value: string) => Boolean(value.trim()) || "Descreva o serviço de coleta.",
    (value: string) => value.length <= 500 || "Use no máximo 500 caracteres.",
];

const categorySummary = computed(() =>
    form.waste_category.length
        ? `${form.waste_category.length} ${form.waste_category.length === 1 ? "categoria selecionada" : "categorias selecionadas"}.`
        : "Nenhuma categoria selecionada.",
);

const citySummary = computed(() =>
    form.cities.length
        ? `${form.cities.length} ${form.cities.length === 1 ? "cidade selecionada" : "cidades selecionadas"}.`
        : "Nenhuma cidade selecionada.",
);

function selected(id: number) {
    return form.waste_category.includes(id);
}

function toggleCategory(id: number) {
    const values = new Set(form.waste_category);
    values.has(id) ? values.delete(id) : values.add(id);
    form.waste_category = [...values];
    errors.waste_category = "";
}

async function completeOnboarding() {
    if (loading.value || catalogLoading.value || catalogError.value) {
        return;
    }
    feedback.value = "";
    errors.service_description = form.service_description.trim()
        ? ""
        : "Descreva o serviço de coleta.";
    errors.cities = form.cities.length ? "" : "Selecione pelo menos uma cidade atendida.";
    errors.waste_category = form.waste_category.length ? "" : "Selecione pelo menos uma categoria.";
    if (errors.service_description || errors.cities || errors.waste_category) {
        await scrollToFirstError(fieldOrder, errors);
        return;
    }

    loading.value = true;
    try {
        await onboardingService.completeCollector({
            service_description: form.service_description.trim(),
            cities: [...new Set(form.cities)],
            waste_category: [...new Set(form.waste_category)],
        });
        await authStore.refreshCurrentUser();
        await router.replace("/area");
    } catch (error) {
        const status = axios.isAxiosError(error) ? error.response?.status : undefined;
        if (status === 401) {
            await router.replace({ name: "login", query: { redirect: "/onboarding/collector" } });
            return;
        }
        if (status === 409) {
            try {
                if ((await authStore.refreshCurrentUser()).onboarding_completed) {
                    await router.replace("/area");
                    return;
                }
            } catch {
                // O formulário permanece disponível se a sessão não pôde ser atualizada.
            }
        }
        const parsed = readFormError(error, "Não foi possível ativar o perfil de coletor.");
        errors.service_description = parsed.fields.service_description ?? "";
        errors.cities = parsed.fields.cities ?? parsed.fields["cities.0"] ?? "";
        errors.waste_category =
            parsed.fields.waste_category ?? parsed.fields["waste_category.0"] ?? "";
        feedback.value =
            status === 403
                ? "Esta conta não possui permissão para ativar um perfil de coletor."
                : status === 409
                  ? "Este perfil de coletor já foi ativado."
                  : parsed.message;
        await scrollToFirstError(fieldOrder, errors);
    } finally {
        loading.value = false;
    }
}

async function loadCategories() {
    catalogLoading.value = true;
    catalogError.value = false;
    feedback.value = "";
    try {
        categories.value = await collectorProfileService.getWasteCategories();
    } catch (error) {
        catalogError.value = true;
        feedback.value = readFormError(
            error,
            "Não foi possível carregar as categorias de resíduos.",
        ).message;
    } finally {
        catalogLoading.value = false;
    }
}

onMounted(loadCategories);
</script>
