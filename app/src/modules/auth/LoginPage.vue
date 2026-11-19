<template>
    <AuthCard
        title-id="login-title"
        icon="login"
        title="Acesse sua conta"
        description="Entre para encontrar pontos de coleta e acompanhar suas solicitações."
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
                id="login-email"
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
            />
            <AuthField
                id="login-password"
                v-model="form.password"
                name="password"
                label="Senha"
                type="password"
                autocomplete="current-password"
                placeholder="Digite sua senha"
                icon="lock_outline"
                :error="Boolean(errors.password)"
                :error-message="errors.password"
                :rules="passwordRules"
            />

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
                    {{ loading ? "Entrando…" : "Entrar" }}
                </span>
            </button>
        </q-form>

        <span class="flex justify-center mt-4 text-sm text-muted">
            Ainda não possui uma conta?
            <RouterLink
                to="/cadastro"
                class="font-extrabold text-eco-700 underline decoration-eco-200 underline-offset-4 hover:text-eco-900 focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-eco-600 ml-1"
            >
                Criar conta
            </RouterLink>
        </span>
    </AuthCard>
</template>

<script setup lang="ts">
import { reactive, ref } from "vue";
import AuthCard from "./components/AuthCard.vue";
import AuthField from "./components/AuthField.vue";

import { useRouter } from "vue-router";
import { useAuthStore } from "./stores/auth.store";
import { readFormError } from "./utils/form-errors";

const router = useRouter();
const authStore = useAuthStore();
const form = reactive({ email: "", password: "" });
const errors = reactive({ email: "", password: "" });
const formError = ref("");
const loading = ref(false);
const emailRules = [
    (value: string) => Boolean(value.trim()) || "Informe seu e-mail.",
    (value: string) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) || "Digite um e-mail válido.",
];
const passwordRules = [(value: string) => Boolean(value) || "Informe sua senha."];

const handleSubmit = async () => {
    loading.value = true;
    formError.value = "";
    errors.email = "";
    errors.password = "";

    try {
        const authentication = await authStore.login(form);
        await router.replace(authStore.destinationFor(authentication.user, authentication));
    } catch (error) {
        const parsed = readFormError(error, "Não foi possível entrar. Verifique seus dados.");
        errors.email = parsed.fields.email ?? "";
        errors.password = parsed.fields.password ?? "";
        formError.value = parsed.message;
    } finally {
        loading.value = false;
    }
};
</script>
