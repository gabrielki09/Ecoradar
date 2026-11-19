<template>
    <OnboardingShell
        title-id="company-onboarding-title"
        icon="business"
        title="Dados da empresa"
        description="Identifique a organização representada pela sua conta. Seus dados de acesso não precisam ser preenchidos novamente."
    >
        <q-form
            ref="formRef"
            class="grid gap-5"
            greedy
            @submit="completeOnboarding"
        >
            <div
                v-if="formError || successMessage"
                :role="successMessage ? 'status' : 'alert'"
                aria-live="polite"
                class="flex items-start gap-3 rounded-xl border p-4 text-sm leading-6"
                :class="
                    successMessage
                        ? 'border-[#c8ddcf] bg-[#f4faf6] text-eco-800'
                        : 'border-[#efbaba] bg-[#fff3f3] text-[#8f2929]'
                "
            >
                <q-icon
                    :name="successMessage ? 'check_circle' : 'error_outline'"
                    size="21px"
                    class="mt-0.5 shrink-0"
                />
                <span>
                    {{ successMessage || formError }}
                </span>
            </div>

            <div class="grid min-w-0 gap-5 sm:grid-cols-2">
                <AuthField
                    id="company-name"
                    v-model="form.company_name"
                    name="company_name"
                    label="Razão social"
                    autocomplete="organization"
                    placeholder="Nome empresarial"
                    icon="business"
                    :rules="companyNameRules"
                    :maxlength="255"
                    :error="Boolean(errors.company_name)"
                    :error-message="errors.company_name"
                />

                <AuthField
                    id="company-document"
                    v-model="form.document"
                    name="document"
                    label="CNPJ"
                    type="tel"
                    inputmode="numeric"
                    autocomplete="off"
                    placeholder="00.000.000/0000-00"
                    icon="badge"
                    mask="##.###.###/####-##"
                    :maxlength="18"
                    :rules="documentRules"
                    :error="Boolean(errors.document)"
                    :error-message="errors.document"
                    @update:model-value="handleAutocompleteCNPJ(form.document)"
                />

                <AuthField
                    id="company-responsible"
                    v-model="form.responsible"
                    name="responsible"
                    label="Responsável pela empresa"
                    autocomplete="name"
                    icon="person_outline"
                    hint="Preenchido com o responsável já autenticado."
                    :maxlength="120"
                    :rules="responsibleRules"
                    :error="Boolean(errors.responsible)"
                    :error-message="errors.responsible"
                />

                <AuthField
                    id="company-contact-email"
                    v-model="form.contact_email"
                    name="contact_email"
                    label="E-mail de contato"
                    type="email"
                    inputmode="email"
                    autocomplete="email"
                    placeholder="contato@empresa.com"
                    icon="mail_outline"
                    :maxlength="255"
                    :rules="optionalEmailRules"
                    :error="Boolean(errors.contact_email)"
                    :error-message="errors.contact_email"
                />

                <AuthField
                    id="company-cep"
                    v-model="form.cep"
                    name="cep"
                    label="CEP"
                    type="tel"
                    inputmode="numeric"
                    autocomplete="postal-code"
                    placeholder="00000-000"
                    icon="markunread_mailbox"
                    mask="#####-###"
                    :maxlength="9"
                    :rules="cepRules"
                    :error="Boolean(errors.cep)"
                    :error-message="errors.cep"
                    @update:model-value="handleAutocompleteCEP(form.cep)"
                />

                <AuthField
                    id="company-neighborhood"
                    v-model="form.neighborhood"
                    name="neighborhood"
                    label="Bairro"
                    autocomplete="address-level3"
                    icon="location_city"
                    :maxlength="255"
                    :error="Boolean(errors.neighborhood)"
                    :error-message="errors.neighborhood"
                />

                <AuthField
                    id="company-address"
                    v-model="form.address"
                    name="address"
                    label="Endereço"
                    autocomplete="street-address"
                    icon="location_on"
                    :maxlength="255"
                    :error="Boolean(errors.address)"
                    :error-message="errors.address"
                />
            </div>

            <button
                type="submit"
                :disabled="loading"
                :aria-busy="loading"
                class="inline-flex min-h-[52px] w-full items-center justify-center gap-2 rounded-xl bg-eco-600 px-5 text-[15px] font-extrabold text-white shadow-[0_10px_24px_rgba(22,128,91,.2)] transition hover:bg-eco-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-eco-600 disabled:cursor-wait disabled:opacity-70"
            >
                <q-spinner
                    v-if="loading"
                    size="22px"
                    color="white"
                />
                <span>
                    {{ loading ? "Concluindo cadastro…" : "Concluir cadastro da empresa" }}
                </span>
            </button>
        </q-form>
    </OnboardingShell>
</template>

<script setup lang="ts">
import { reactive, ref } from "vue";
import axios from "axios";
import type { QForm } from "quasar";

import { useRouter } from "vue-router";
import OnboardingShell from "../components/OnboardingShell.vue";
import AuthField from "@/modules/auth/components/AuthField.vue";
import { onboardingService } from "../services/onboarding.service";
import type { CompanyOnboardingPayload } from "../types/onboarding.types";
import { useAuthStore } from "@/modules/auth/stores/auth.store";
import { readFormError } from "@/modules/auth/utils/form-errors";
import { scrollToFirstError } from "@/helpers/pages/scrollToFirstError";
import { companyLookupService } from "../services/company-lookup.service.js";

type CompanyForm = Record<keyof CompanyOnboardingPayload, string>;

const authStore = useAuthStore();
const router = useRouter();
const formRef = ref<QForm>();
const loading = ref(false);
const formError = ref("");
const successMessage = ref("");

const form = reactive<CompanyForm>({
    company_name: "",
    document: "",
    responsible: authStore.user?.name ?? "",
    cep: "",
    neighborhood: "",
    address: "",
    contact_email: "",
});

const errors = reactive<CompanyForm>({
    company_name: "",
    document: "",
    responsible: "",
    cep: "",
    neighborhood: "",
    address: "",
    contact_email: "",
});

const fieldOrder: Array<keyof CompanyForm> = [
    "company_name",
    "document",
    "responsible",
    "contact_email",
    "cep",
    "neighborhood",
    "address",
];

const companyNameRules = [
    (value: string) => Boolean(value.trim()) || "Informe a razão social.",
    (value: string) => value.length <= 255 || "Use no máximo 255 caracteres.",
];
const documentRules = [
    (value: string) => Boolean(value.trim()) || "Informe o CNPJ.",
    (value: string) => value.replace(/\D/g, "").length === 14 || "Digite os 14 números do CNPJ.",
];
const responsibleRules = [
    (value: string) => Boolean(value.trim()) || "Informe o responsável.",
    (value: string) => value.length <= 120 || "Use no máximo 120 caracteres.",
];
const optionalEmailRules = [
    (value: string) =>
        !value || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) || "Digite um e-mail válido.",
];
const cepRules = [
    (value: string) =>
        !value || value.replace(/\D/g, "").length === 8 || "Digite os 8 números do CEP.",
];

function clearErrors() {
    for (const field of fieldOrder) errors[field] = "";
}

async function completeOnboarding() {
    formError.value = "";
    successMessage.value = "";
    clearErrors();

    const valid = await formRef.value?.validate();
    if (!valid) {
        return;
    }

    loading.value = true;
    const optional = (value: string) => value.trim() || null;

    try {
        await onboardingService.completeCompany({
            company_name: form.company_name.trim(),
            document: form.document.replace(/\D/g, ""),
            cep: form.cep.replace(/\D/g, ""),
            responsible: form.responsible.trim(),
            neighborhood: optional(form.neighborhood),
            address: optional(form.address),
            contact_email: optional(form.contact_email),
        });
        successMessage.value = "Empresa cadastrada com sucesso. Preparando sua área…";
        await authStore.refreshCurrentUser();
        await router.replace("/area");
    } catch (error) {
        const status = axios.isAxiosError(error) ? error.response?.status : undefined;

        if (status === 401) {
            await router.replace({ name: "login", query: { redirect: "/onboarding/company" } });
            return;
        }

        if (status === 409) {
            try {
                const user = await authStore.refreshCurrentUser();
                if (user.onboarding_completed) {
                    await router.replace("/area");
                    return;
                }
            } catch {
                // Mantém o conflito original caso a sessão não possa ser atualizada.
            }
        }

        const parsed = readFormError(error, "Não foi possível concluir o cadastro da empresa.");
        for (const field of fieldOrder) errors[field] = parsed.fields[field] ?? "";
        formError.value =
            status === 403
                ? "Esta conta não possui permissão para cadastrar uma empresa."
                : parsed.message;

        if (status === 422) {
            await scrollToFirstError(fieldOrder, errors);
        }
    } finally {
        loading.value = false;
    }
}

const handleAutocompleteCNPJ = async (cnpj: string) => {
    if (
        form.company_name !== "" ||
        form.contact_email !== "" ||
        form.address !== "" ||
        form.neighborhood !== ""
    )
        return;

    if (cnpj.replace(/\D/g, "").length === 14) {
        const { data } = await companyLookupService.findCompanyByCnpj(cnpj);

        form.company_name = data.alias;
        form.contact_email = data.emails[0]?.address ?? "";

        if (form.cep === "") {
            form.cep = data.address.zip;
            form.address = data.address.district;
            form.neighborhood = data.address.street;
        }

        return;
    }
};

const handleAutocompleteCEP = async (cep: string) => {
    if (form.address !== "" && form.neighborhood !== "") {
        return;
    }

    if (cep.replace(/\D/g, "").length === 8) {
        const { data } = await companyLookupService.findAddressByCep(cep);

        form.address = data.street;
        form.neighborhood = data.neighborhood;

        return;
    }
};
</script>
