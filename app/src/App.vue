<template>
    <div
        v-if="authStore.isChecking"
        class="grid min-h-screen place-items-center bg-cream text-eco-800"
        role="status"
        aria-live="polite"
    >
        <div class="flex flex-col items-center gap-4 text-center">
            <q-spinner-orbit
                size="42px"
                color="green-8"
            />
            <p class="m-0 text-sm font-semibold">
                Verificando sua sessão…
            </p>
        </div>
    </div>

    <router-view v-else />
</template>

<script setup lang="ts">
import { onBeforeMount } from "vue";
import { useAuthStore } from "@/modules/auth/stores/auth.store";

const authStore = useAuthStore();

onBeforeMount(() => {
    authStore.restoreSession();
});
</script>
