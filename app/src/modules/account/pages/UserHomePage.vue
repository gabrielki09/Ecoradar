<template>
    <q-page class="bg-[linear-gradient(180deg,#f3faf6_0%,#fbfaf5_55%)] px-4 py-10 sm:px-6 sm:py-14">
        <main class="mx-auto w-full max-w-[1120px]">
            <section
                class="overflow-hidden rounded-[24px] bg-[linear-gradient(120deg,#165c45,#18815d)] p-7 text-white shadow-eco sm:p-10"
            >
                <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#bce8c9]">
                    Sua área EcoRadar
                </span>
                <h1
                    class="mb-3 mt-3 text-[clamp(2rem,6vw,3.25rem)] font-extrabold leading-tight tracking-[-0.04em]"
                >
                    Olá, {{ firstName }}.
                </h1>
                <p class="m-0 max-w-2xl text-sm leading-6 text-white/75 sm:text-base">
                    Sua conta está pronta. Aqui você encontra atalhos para começar a descartar com
                    mais consciência.
                </p>
                <div
                    v-if="authStore.canRequest"
                    class="mt-6 flex flex-wrap gap-3"
                >
                    <RouterLink
                        to="/area/coletas/mine"
                        class="rounded-xl bg-white px-5 py-3 text-sm font-extrabold no-underline shadow-sm"
                    >
                        <span class="text-eco-700">
                            Minhas solicitações
                        </span>
                    </RouterLink>
                    <RouterLink
                        to="/area/coletas/nova"
                        class="rounded-xl border border-white/70 px-5 py-3 text-sm font-extrabold text-white no-underline"
                    >
                        Criar solicitação
                    </RouterLink>
                </div>
            </section>

            <section
                class="mt-7 grid gap-4 md:grid-cols-3"
                aria-label="Próximos passos"
            >
                <RouterLink
                    v-if="authStore.canRequest"
                    to="/area/coletas/mine"
                    class="rounded-2xl border border-line bg-white p-6 text-inherit no-underline shadow-sm transition hover:-translate-y-1 hover:shadow-eco"
                >
                    <span
                        class="grid size-11 place-items-center rounded-xl bg-eco-100 text-eco-700"
                    >
                        <q-icon
                            name="assignment"
                            size="24px"
                        />
                    </span>
                    <h2 class="mb-2 mt-5 text-lg font-extrabold text-eco-900">
                        Minhas solicitações
                    </h2>
                    <p class="m-0 text-sm leading-6 text-muted">
                        Acompanhe seus pedidos, status e datas de coleta.
                    </p>
                </RouterLink>
                <RouterLink
                    v-if="authStore.canRequest"
                    to="/area/coletas/nova"
                    class="rounded-2xl border border-line bg-white p-6 text-inherit no-underline shadow-sm transition hover:-translate-y-1 hover:shadow-eco"
                >
                    <span
                        class="grid size-11 place-items-center rounded-xl bg-eco-100 text-eco-700"
                    >
                        <q-icon
                            name="add_circle"
                            size="24px"
                        />
                    </span>
                    <h2 class="mb-2 mt-5 text-lg font-extrabold text-eco-900">
                        Criar solicitação
                    </h2>
                    <p class="m-0 text-sm leading-6 text-muted">
                        Informe o endereço e os resíduos que deseja entregar.
                    </p>
                </RouterLink>
                <RouterLink
                    v-if="authStore.user?.profile_type === 'collector'"
                    to="/area/perfil-coleta"
                    class="rounded-2xl border border-line bg-white p-6 text-inherit no-underline shadow-sm transition hover:-translate-y-1 hover:shadow-eco focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-eco-600"
                >
                    <span
                        class="grid size-11 place-items-center rounded-xl bg-eco-100 text-eco-700"
                    >
                        <q-icon
                            name="tune"
                            size="24px"
                        />
                    </span>
                    <h2 class="mb-2 mt-5 text-lg font-extrabold text-eco-900">
                        Perfil de coleta
                    </h2>
                    <p class="m-0 text-sm leading-6 text-muted">
                        Configure a cidade atendida e os resíduos que você aceita.
                    </p>
                </RouterLink>

                <RouterLink
                    to="/pontos"
                    class="rounded-2xl border border-line bg-white p-6 text-inherit no-underline shadow-sm transition hover:-translate-y-1 hover:shadow-eco focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-eco-600"
                >
                    <span
                        class="grid size-11 place-items-center rounded-xl bg-eco-100 text-eco-700"
                    >
                        <q-icon
                            name="location_on"
                            size="24px"
                        />
                    </span>
                    <h2 class="mb-2 mt-5 text-lg font-extrabold text-eco-900">
                        Encontrar pontos
                    </h2>
                    <p class="m-0 text-sm leading-6 text-muted">
                        Consulte os pontos de coleta cadastrados no EcoRadar.
                    </p>
                </RouterLink>

                <button
                    type="button"
                    aria-haspopup="dialog"
                    class="cursor-pointer rounded-2xl border border-line bg-white p-6 text-left text-inherit no-underline shadow-sm transition hover:-translate-y-1 hover:shadow-eco focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-eco-600"
                    @click="isMaterialsGuideOpen = true"
                >
                    <span
                        class="grid size-11 place-items-center rounded-xl bg-eco-100 text-eco-700"
                    >
                        <q-icon
                            name="recycling"
                            size="24px"
                        />
                    </span>
                    <h2 class="mb-2 mt-5 text-lg font-extrabold text-eco-900">
                        Guia de descarte
                    </h2>
                    <p class="m-0 text-sm leading-6 text-muted">
                        Veja quais materiais podem receber uma destinação adequada.
                    </p>
                </button>

            </section>
        </main>
        <MaterialsGuideDialog v-model="isMaterialsGuideOpen" />
    </q-page>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { useAuthStore } from "@/modules/auth/stores/auth.store";
import MaterialsGuideDialog from "@/modules/home/components/MaterialsGuideDialog.vue";

const authStore = useAuthStore();
const isMaterialsGuideOpen = ref(false);
const firstName = computed(() => authStore.user?.name.trim().split(/\s+/)[0] || "bem-vindo");
</script>
