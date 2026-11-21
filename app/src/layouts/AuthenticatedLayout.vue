<template>
    <q-layout
        view="hHh Lpr fFf"
        class="bg-cream text-ink"
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

                <RouterLink
                    v-if="authStore.canRequest"
                    to="/area/coletas/mine"
                    class="mr-4 text-sm font-bold text-eco-900"
                >
                    Minhas solicitações
                </RouterLink>

                <RouterLink
                    v-if="authStore.canProvide"
                    to="/area/coletas/all"
                    class="mr-4 text-sm font-bold text-eco-900"
                >
                    Todas as requisições
                </RouterLink>

                <RouterLink
                    v-if="authStore.canProvide"
                    to="/area/coletas/available"
                    class="mr-4 text-sm font-bold text-eco-900"
                >
                    Aplicáveis
                </RouterLink>
                <RouterLink
                    v-if="authStore.canProvide"
                    to="/area/coletas/for-me"
                    class="mr-4 text-sm font-bold text-eco-900"
                >
                    Minhas coletas
                </RouterLink>
                <RouterLink to="/pontos" class="mr-4 text-sm font-bold text-eco-900">
                    Pontos de coleta
                </RouterLink>
                <RouterLink v-if="authStore.canProvide" to="/pontos/meus" class="mr-4 text-sm font-bold text-eco-900">
                    Meus pontos
                </RouterLink>

                <q-select
                    v-if="
                        authStore.canProvide &&
                        authStore.user?.profile_type === 'company' &&
                        collectionStore.companies.length
                    "
                    :model-value="collectionStore.selectedCompanyId"
                    :options="companyOptions"
                    emit-value
                    map-options
                    outlined
                    dense
                    label="Empresa"
                    class="mr-3 min-w-40"
                    @update:model-value="collectionStore.selectCompany"
                />

                <div class="mr-3 hidden text-right sm:block">
                    <strong class="block text-sm text-eco-900">
                        {{ authStore.user?.name }}
                    </strong>
                    <span class="text-xs text-muted">
                        {{ profileLabel }}
                    </span>
                </div>
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
import { computed, ref } from "vue";

import { useRouter } from "vue-router";
import { useAuthStore } from "@/modules/auth/stores/auth.store";
import { useCollectionStore } from "@/modules/collection-requests/store";

const router = useRouter();
const authStore = useAuthStore();
const collectionStore = useCollectionStore();
const leaving = ref(false);

const profileLabel = computed(
    () =>
        ({
            common: "Usuário comum",
            collector: "Coletor individual",
            company: "Empresa",
        })[authStore.user?.profile_type ?? "common"],
);

const companyOptions = computed(() =>
    collectionStore.companies.map((company) => ({
        label: company.company_name,
        value: company.id,
    })),
);

async function handleLogout() {
    leaving.value = true;
    try {
        await authStore.logout();
        await router.replace("/login");
    } finally {
        leaving.value = false;
    }
}
</script>
