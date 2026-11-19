<template>
    <AuthCard
        title-id="register-title"
        icon="person_add"
        eyebrow="Comece sua jornada"
        title="Crie sua conta"
        description="Cadastre a pessoa responsável pela conta. Os dados do coletor ou da empresa serão preenchidos depois."
    >
        <q-form
            class="grid gap-5"
            greedy
            @submit="handleSubmit"
        >
            <div
                v-if="formError"
                role="alert"
                aria-live="polite"
                class="flex items-start gap-3 rounded-xl border border-[#efbaba] bg-[#fff3f3] p-3.5 text-sm text-[#8f2929]"
            >
                <q-icon
                    name="error_outline"
                    size="21px"
                />
                <span>
                    {{ formError }}
                </span>
            </div>

            <AuthField
                id="register-name"
                v-model="form.name"
                name="name"
                label="Nome completo"
                autocomplete="name"
                placeholder="Como você gostaria de ser chamado?"
                icon="person_outline"
                :error="Boolean(errors.name)"
                :error-message="errors.name"
                :rules="nameRules"
                :maxlength="255"
            />

            <AuthField
                id="register-email"
                v-model="form.email"
                name="email"
                label="E-mail"
                type="email"
                inputmode="email"
                autocomplete="email"
                placeholder="voce@exemplo.com"
                icon="mail_outline"
                :error="Boolean(errors.email)"
                :error-message="errors.email"
                :rules="emailRules"
                :maxlength="255"
            />

            <AuthField
                id="register-document"
                v-model="form.document"
                name="document"
                label="CPF"
                type="tel"
                inputmode="numeric"
                autocomplete="off"
                placeholder="000.000.000-00"
                icon="badge"
                hint="Informe o CPF da pessoa responsável pela conta."
                :error="Boolean(errors.document)"
                :error-message="errors.document"
                :rules="documentRules"
                :maxlength="14"
                mask="###.###.###-##"
            />

            <AuthField
                id="register-password"
                v-model="form.password"
                name="password"
                label="Senha"
                type="password"
                autocomplete="new-password"
                placeholder="Crie uma senha segura"
                icon="lock_outline"
                hint="Use pelo menos 8 caracteres."
                :error="Boolean(errors.password)"
                :error-message="errors.password"
                :rules="passwordRules"
                :maxlength="255"
            />

            <fieldset
                id="register-profile_type"
                class="m-0 min-w-0 border-0 p-0"
            >
                <legend class="mb-2 block text-sm font-bold text-eco-900">
                    Tipo de perfil
                </legend>
                <div class="grid gap-2.5 sm:grid-cols-3">
                    <label
                        v-for="profile in profiles"
                        :key="profile.value"
                        class="group relative cursor-pointer rounded-xl border bg-white p-3.5 transition hover:border-eco-600 hover:bg-eco-50 has-checked:border-eco-600 has-checked:bg-eco-50 has-checked:ring-2 has-checked:ring-eco-100 focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-eco-600"
                    >
                        <input
                            v-model="form.profile_type"
                            class="peer sr-only"
                            type="radio"
                            name="profile_type"
                            :value="profile.value"
                        />
                        <span
                            class="mb-2 grid size-9 place-items-center rounded-lg bg-[#f1f6f3] text-muted transition peer-checked:bg-eco-600 peer-checked:text-white"
                        >
                            <q-icon
                                :name="profile.icon"
                                size="20px"
                            />
                        </span>
                        <span class="block text-sm font-extrabold leading-tight text-eco-900">
                            {{ profile.label }}
                        </span>
                        <span class="mt-1 block text-[11px] leading-4 text-muted">
                            {{ profile.description }}
                        </span>
                        <q-icon
                            name="check_circle"
                            size="18px"
                            class="absolute right-2.5 top-2.5 scale-75 text-eco-600 opacity-0 transition peer-checked:scale-100 peer-checked:opacity-100"
                        />
                    </label>
                </div>

                <p
                    v-if="errors.profile_type"
                    role="alert"
                    class="mt-1.5 flex items-center gap-1.5 text-xs font-medium text-[#a83232]"
                >
                    <q-icon
                        name="error_outline"
                        size="16px"
                    />
                    {{ errors.profile_type }}
                </p>
            </fieldset>

            <button
                type="submit"
                :disabled="loading"
                :aria-busy="loading"
                class="mt-1 inline-flex min-h-[52px] w-full items-center justify-center gap-2 rounded-xl bg-eco-600 px-5 text-[15px] font-extrabold text-white shadow-[0_10px_24px_rgba(22,128,91,.2)] transition hover:bg-eco-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-eco-600 disabled:cursor-wait disabled:opacity-70"
            >
                <q-spinner
                    v-if="loading"
                    size="22px"
                    color="white"
                />
                <span>
                    {{ loading ? "Criando conta…" : "Criar conta" }}
                </span>
            </button>
        </q-form>

        <span class="flex justify-center mt-4 text-sm text-muted">
            Já possui uma conta?
            <RouterLink
                to="/login"
                class="font-extrabold text-eco-700 underline decoration-eco-200 underline-offset-4 hover:text-eco-900 focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-eco-600 ml-1"
            >
                Acessar login
            </RouterLink>
        </span>
    </AuthCard>
</template>

<script setup lang="ts">
import { reactive, ref } from "vue";
import AuthCard from "./components/AuthCard.vue";
import AuthField from "./components/AuthField.vue";
import type { ProfileType } from "./types/profile/ProfileTypes";
import validateCPF from "@/helpers/document/validateCPF";
import type { RegisterPayload } from "./types/auth.types";

import { useRouter } from "vue-router";
import { scrollToFirstError } from "@/helpers/pages/scrollToFirstError";
import { useAuthStore } from "./stores/auth.store";
import { readFormError } from "./utils/form-errors";

const profiles: Array<{ value: ProfileType; label: string; description: string; icon: string }> = [
    {
        value: "common",
        label: "Usuário comum",
        description: "Quero descartar corretamente.",
        icon: "person",
    },
    {
        value: "collector",
        label: "Coletor individual",
        description: "Realizo coletas por conta própria.",
        icon: "local_shipping",
    },
    {
        value: "company",
        label: "Empresa",
        description: "Represento uma organização.",
        icon: "business",
    },
];

const form = reactive<RegisterPayload>({
    name: "",
    email: "",
    document: "",
    profile_type: "common" as ProfileType,
    password: "",
});

const errors = reactive({ name: "", email: "", document: "", profile_type: "", password: "" });
const formError = ref("");
const loading = ref(false);

const router = useRouter();
const authStore = useAuthStore();

const nameRules = [
    (value: string) => Boolean(value.trim()) || "Informe seu nome completo.",
    (value: string) => value.trim().split(/\s+/).length >= 2 || "Digite nome e sobrenome.",
];

const emailRules = [
    (value: string) => Boolean(value.trim()) || "Informe seu e-mail.",
    (value: string) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) || "Digite um e-mail válido.",
];

const documentRules = [
    (value: string) => Boolean(value.trim()) || "Informe seu CPF.",
    (value: string) => validateCPF(value) || "Digite um CPF válido.",
];

const passwordRules = [
    (value: string) => Boolean(value) || "Crie uma senha.",
    (value: string) => value.length >= 8 || "A senha deve ter pelo menos 8 caracteres.",
];

const handleSubmit = async () => {
    formError.value = "";
    loading.value = true;

    const fields = Object.keys(errors) as Array<keyof typeof errors>;

    for (const field of fields) {
        errors[field] = "";
    }

    try {
        const authentication = await authStore.register(form);
        await router.replace(authStore.destinationFor(authentication.user, authentication));
    } catch (error) {
        const parsed = readFormError(error, "Não foi possível criar sua conta.");
        for (const field of fields) errors[field] = parsed.fields[field] ?? "";
        formError.value = parsed.message;

        await scrollToFirstError(
            ["name", "email", "document", "password", "profile_type"] as const,
            errors,
        );
    } finally {
        loading.value = false;
    }
};
</script>
