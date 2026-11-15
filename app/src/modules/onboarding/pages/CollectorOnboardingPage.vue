<template>
    <OnboardingShell title-id="collector-onboarding-title" icon="local_shipping" title="Ative seu perfil de coletor"
        description="Sua conta já está pronta. Confirme abaixo para ativar o perfil de coletor individual no EcoRadar.">
        <div v-if="feedback" :role="feedbackKind === 'success' ? 'status' : 'alert'" aria-live="polite"
            class="mb-6 flex items-start gap-3 rounded-xl border p-4 text-sm leading-6"
            :class="feedbackKind === 'success' ? 'border-[#c8ddcf] bg-[#f4faf6] text-eco-800' : 'border-[#efbaba] bg-[#fff3f3] text-[#8f2929]'">
            <q-icon :name="feedbackKind === 'success' ? 'check_circle' : 'error_outline'" size="21px"
                class="mt-0.5 shrink-0" />
            <span>{{ feedback }}</span>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <article class="rounded-2xl border border-line bg-eco-50 p-5">
                <q-icon name="person" size="25px" class="text-eco-700" />
                <h2 class="mb-2 mt-3 text-base font-extrabold text-eco-900">Conta confirmada</h2>
                <p class="m-0 break-words text-sm leading-6 text-muted">{{ authStore.user?.name }}, seus dados de acesso
                    já estão vinculados a esta etapa.</p>
            </article>
            <article class="rounded-2xl border border-line bg-white p-5">
                <q-icon name="verified" size="25px" class="text-eco-700" />
                <h2 class="mb-2 mt-3 text-base font-extrabold text-eco-900">Ativação imediata</h2>
                <p class="m-0 text-sm leading-6 text-muted">Nenhuma informação adicional é necessária nesta versão. Ao
                    continuar, seu perfil individual será ativado.</p>
            </article>
        </div>

        <button type="button" :disabled="loading" :aria-busy="loading" @click="completeOnboarding"
            class="mt-8 inline-flex min-h-[52px] w-full items-center justify-center gap-2 rounded-xl bg-eco-600 px-5 text-[15px] font-extrabold text-white shadow-[0_10px_24px_rgba(22,128,91,.2)] transition hover:bg-eco-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-eco-600 disabled:cursor-wait disabled:opacity-70">
            <q-spinner v-if="loading" size="22px" color="white" />
            <span>{{ loading ? 'Ativando perfil…' : 'Ativar perfil de coletor' }}</span>
        </button>
    </OnboardingShell>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import OnboardingShell from '../components/OnboardingShell.vue';
import { onboardingService } from '../services/onboarding.service';
import { useAuthStore } from '@/modules/auth/stores/auth.store';
import { readFormError } from '@/modules/auth/utils/form-errors';

const authStore = useAuthStore();
const router = useRouter();
const loading = ref(false);
const feedback = ref('');
const feedbackKind = ref<'error' | 'success'>('error');

async function completeOnboarding() {
    feedback.value = '';
    loading.value = true;

    try {
        await onboardingService.completeCollector();
        feedbackKind.value = 'success';
        feedback.value = 'Perfil ativado com sucesso. Preparando sua área…';
        await authStore.refreshCurrentUser();
        await router.replace('/area');
    } catch (error) {
        const status = axios.isAxiosError(error) ? error.response?.status : undefined;

        if (status === 401) {
            await router.replace({ name: 'login', query: { redirect: '/onboarding/collector' } });
            return;
        }

        if (status === 409) {
            try {
                const user = await authStore.refreshCurrentUser();
                if (user.onboarding_completed) {
                    await router.replace('/area');
                    return;
                }
            } catch {
                // Mantém o erro original caso a sessão não possa ser atualizada.
            }
        }

        feedbackKind.value = 'error';
        feedback.value = status === 403
            ? 'Esta conta não possui permissão para ativar um perfil de coletor.'
            : readFormError(error, 'Não foi possível ativar o perfil de coletor.').message;
    } finally {
        loading.value = false;
    }
}
</script>
