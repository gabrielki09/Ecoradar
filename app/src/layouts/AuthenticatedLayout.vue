<template>
    <q-layout
        view="hHh Lpr fFf"
        class="eco-account bg-cream text-ink"
    >
        <q-header class="border-b border-eco-900/10 bg-white/95 text-ink backdrop-blur-xl">
            <q-toolbar class="mx-auto min-h-[72px] w-[min(1120px,calc(100%-32px))] p-0">
                <RouterLink
                    to="/area"
                    class="inline-flex items-center gap-2.5 text-xl font-extrabold text-eco-900 no-underline"
                >
                    <span
                        class="grid size-9 -rotate-3 place-items-center rounded-[11px_11px_11px_4px] bg-eco-600 text-white"
                    >
                        <q-icon
                            class="rotate-3"
                            name="eco"
                            size="21px"
                        />
                    </span>
                    <span>
                        EcoRadar
                    </span>
                </RouterLink>

                <q-space />

                <nav
                    class="eco-account__desktop-nav"
                    aria-label="Navegação da conta"
                >
                    <RouterLink
                        v-for="item in navigation"
                        :key="item.label"
                        :to="item.to"
                        class="text-sm font-bold text-eco-900"
                    >
                        {{ item.label }}
                    </RouterLink>
                </nav>

                <q-btn
                    v-if="navigation.length"
                    flat
                    round
                    icon="menu"
                    aria-label="Menu da conta"
                    class="eco-account__mobile-menu"
                >
                    <q-menu>
                        <q-list style="min-width: 210px">
                            <q-item
                                v-for="item in navigation"
                                :key="item.label"
                                :to="item.to"
                                clickable
                                v-close-popup
                            >
                                <q-item-section>{{ item.label }}</q-item-section>
                            </q-item>
                        </q-list>
                    </q-menu>
                </q-btn>

                <q-btn
                    flat
                    round
                    icon="logout"
                    aria-label="Sair da conta"
                    :loading="leaving"
                    @click="handleLogout"
                />
            </q-toolbar>
        </q-header>

        <q-page-container>
            <router-view />
        </q-page-container>
    </q-layout>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";

import { useRoute, useRouter } from "vue-router";
import { useQuasar } from "quasar";
import { useAuthStore } from "@/modules/auth/stores/auth.store";
import { usePointStore } from "@/modules/collection-points/store";

const route = useRoute();
const router = useRouter();
const $q = useQuasar();
const authStore = useAuthStore();
const pointStore = usePointStore();
const leaving = ref(false);

const navigation = computed(() => {
    if (authStore.pendingOnboarding) return [];

    const links = [{ label: "Pontos de coleta", to: "/pontos" }];
    if (authStore.canRequest) {
        links.unshift({ label: "Minhas solicitações", to: "/area/coletas/mine" });
    }
    if (authStore.canProvide) {
        links.unshift(
            { label: "Todas as solicitações", to: "/area/coletas/all" },
            { label: "Aplicáveis", to: "/area/coletas/available" },
            { label: "Minhas coletas", to: "/area/coletas/for-me" },
        );
        if (authStore.user?.profile_type === "collector" || pointStore.companies.length) {
            links.push({ label: "Meus pontos", to: "/pontos/meus" });
            links.push(authStore.user?.profile_type === "collector"
                ? { label: "Perfil de coleta", to: "/area/perfil-coleta" }
                : { label: "Perfil da empresa", to: "/area/perfil-empresa" });
        }
    }

    return links;
});

watch(() => authStore.isAuthenticated, (authenticated) => {
    if (!authenticated && !leaving.value) {
        void router.replace({ name: "login", query: { redirect: route.fullPath } });
    }
});

async function handleLogout() {
    if (leaving.value) return;
    leaving.value = true;
    try {
        await authStore.logout();
    } catch {
        $q.notify({ type: "warning", message: "Sessão encerrada neste dispositivo. A API não confirmou a revogação." });
    } finally {
        await router.replace({ name: "login" });
        leaving.value = false;
    }
}
</script>
