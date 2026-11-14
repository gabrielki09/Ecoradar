<template>
    <q-layout view="hHh Lpr fFf" class="bg-cream">
        <q-header class="border-b border-eco-900/10 bg-white/95 text-ink backdrop-blur-2xl">
            <q-toolbar class="mx-auto min-h-[76px] w-[min(1180px,calc(100%-40px))] gap-[38px] p-0 max-[760px]:w-[min(calc(100%-30px),600px)] max-[460px]:min-h-[68px]">
                <RouterLink class="inline-flex items-center gap-2.5 whitespace-nowrap text-[21px] font-extrabold text-eco-900 no-underline max-[460px]:text-[19px]" to="/" aria-label="EcoRadar — página inicial">
                    <span class="grid size-[38px] -rotate-3 place-items-center rounded-[12px_12px_12px_4px] bg-eco-600 text-white max-[460px]:size-[35px]" aria-hidden="true"><q-icon class="rotate-3" name="eco" size="23px" /></span>
                    <span>Eco<span class="text-eco-600">Radar</span></span>
                </RouterLink>

                <nav class="flex flex-1 justify-center gap-[30px] max-[980px]:hidden" aria-label="Navegação principal">
                    <RouterLink v-for="item in navigation" :key="item.label" :to="item.to" class="relative py-[27px] pb-[25px] text-sm font-semibold text-[#536860] no-underline hover:text-eco-700 [&.router-link-exact-active]:text-eco-700 [&.router-link-exact-active]:after:absolute [&.router-link-exact-active]:after:inset-x-0 [&.router-link-exact-active]:after:bottom-[17px] [&.router-link-exact-active]:after:h-0.5 [&.router-link-exact-active]:after:rounded-sm [&.router-link-exact-active]:after:bg-eco-600 [&.router-link-exact-active]:after:content-['']">
                        {{ item.label }}
                    </RouterLink>
                </nav>

                <div class="flex items-center gap-2 max-[980px]:hidden">
                    <q-btn flat no-caps label="Entrar" class="font-semibold text-eco-800" />
                    <q-btn unelevated no-caps label="Criar conta" class="min-h-[43px] rounded-[11px] bg-eco-600 px-[17px] font-bold text-white hover:bg-eco-700" />
                </div>

                <q-btn flat round dense icon="menu" class="ml-auto hidden text-eco-800 max-[980px]:inline-flex" aria-label="Abrir menu" @click="drawerOpen = true" />
            </q-toolbar>
        </q-header>

        <q-drawer v-model="drawerOpen" side="right" overlay bordered class="mobile-drawer">
            <div class="flex items-center justify-between p-[22px]">
                <span class="text-[21px] font-extrabold text-eco-900">Eco<span class="text-eco-600">Radar</span></span>
                <q-btn flat round dense icon="close" aria-label="Fechar menu" @click="drawerOpen = false" />
            </div>
            <q-list padding>
                <q-item v-for="item in navigation" :key="item.label" v-ripple clickable :to="item.to" @click="drawerOpen = false">
                    <q-item-section>{{ item.label }}</q-item-section>
                </q-item>
            </q-list>
            <div class="grid gap-2.5 p-[18px]">
                <q-btn outline no-caps label="Entrar" class="full-width" />
                <q-btn unelevated no-caps label="Criar conta" class="full-width min-h-[43px] rounded-[11px] bg-eco-600 px-[17px] font-bold text-white hover:bg-eco-700" />
            </div>
        </q-drawer>

        <q-page-container><router-view /></q-page-container>

        <q-footer class="static bg-[#11382d] text-white/70">
            <div class="mx-auto grid w-[min(1180px,calc(100%-40px))] grid-cols-[1.2fr_.8fr_auto] items-center gap-[45px] py-[50px] max-[980px]:grid-cols-2 max-[760px]:w-[min(calc(100%-30px),600px)] max-[760px]:grid-cols-1">
                <div>
                    <div class="inline-flex items-center gap-2.5 whitespace-nowrap text-[21px] font-extrabold text-white">
                        <span class="grid size-[38px] -rotate-3 place-items-center rounded-[12px_12px_12px_4px] bg-eco-600 text-white"><q-icon class="rotate-3" name="eco" size="21px" /></span>
                        <span>Eco<span class="text-eco-600">Radar</span></span>
                    </div>
                    <p class="mt-[15px] mb-0 max-w-[360px] text-xs">Conectando pessoas a um futuro com menos resíduos e mais consciência.</p>
                </div>
                <div class="grid grid-cols-2 gap-x-7 gap-y-2.5">
                    <RouterLink v-for="item in navigation.slice(0, 4)" :key="item.label" :to="item.to" class="text-xs no-underline hover:text-white">{{ item.label }}</RouterLink>
                </div>
                <div class="grid gap-[7px] text-right text-[11px] max-[980px]:col-span-full max-[980px]:text-left max-[760px]:col-auto">
                    <span>Projeto acadêmico · Concórdia, SC</span>
                    <span>© {{ currentYear }} EcoRadar</span>
                </div>
            </div>
        </q-footer>
    </q-layout>
</template>

<script setup lang="ts">
    import { ref } from 'vue';

    const drawerOpen = ref(false);
    const currentYear = new Date().getFullYear();
    const navigation = [
        { label: 'Início', to: '/' },
        { label: 'Pontos de coleta', to: { path: '/', hash: '#pontos' } },
        { label: 'Como funciona', to: { path: '/', hash: '#como-funciona' } },
        { label: 'Guia de descarte', to: { path: '/', hash: '#materiais' } },
        { label: 'Categorias', to: '/categories' },
    ];
</script>
