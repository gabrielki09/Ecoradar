<template>
    <q-layout view="hHh Lpr fFf" class="bg-cream text-ink">
        <q-header class="border-b border-eco-900/10 bg-white/95 text-ink backdrop-blur-xl">
            <q-toolbar class="mx-auto min-h-[72px] w-[min(1120px,calc(100%-32px))] p-0">
                <RouterLink to="/area"
                    class="inline-flex items-center gap-2.5 text-xl font-extrabold text-eco-900 no-underline">
                    <span
                        class="grid size-9 -rotate-3 place-items-center rounded-[11px_11px_11px_4px] bg-eco-600 text-white"><q-icon
                            class="rotate-3" name="eco" size="21px" /></span>
                    <span>EcoRadar</span>
                </RouterLink>

                <q-space />

                <div class="mr-3 hidden text-right sm:block">
                    <strong class="block text-sm text-eco-900">{{ authStore.user?.name }}</strong>
                    <span class="text-xs text-muted">{{ profileLabel }}</span>
                </div>
                <q-btn flat round icon="logout" aria-label="Sair da conta" :loading="leaving" @click="handleLogout" />
            </q-toolbar>
        </q-header>

        <q-page-container>
            <router-view />
        </q-page-container>
    </q-layout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/modules/auth/stores/auth.store';

const router = useRouter();
const authStore = useAuthStore();
const leaving = ref(false);

const profileLabel = computed(() => ({
    common: 'Usuário comum',
    collector: 'Coletor individual',
    company: 'Empresa',
}[authStore.user?.profile_type ?? 'common']));

async function handleLogout() {
    leaving.value = true;
    try {
        await authStore.logout();
        await router.replace('/login');
    } finally {
        leaving.value = false;
    }
}
</script>
