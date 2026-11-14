<template>
    <q-layout view="hHh Lpr fFf" class="eco-layout">
        <q-header class="eco-header">
            <q-toolbar class="eco-container eco-toolbar">
                <RouterLink class="brand" to="/" aria-label="EcoRadar — página inicial">
                    <span class="brand__mark" aria-hidden="true"><q-icon name="eco" size="23px" /></span>
                    <span>Eco<span>Radar</span></span>
                </RouterLink>

                <nav class="desktop-nav" aria-label="Navegação principal">
                    <RouterLink v-for="item in navigation" :key="item.label" :to="item.to">
                        {{ item.label }}
                    </RouterLink>
                </nav>

                <div class="desktop-actions">
                    <q-btn flat no-caps label="Entrar" class="login-button" />
                    <q-btn unelevated no-caps label="Criar conta" class="primary-button" />
                </div>

                <q-btn flat round dense icon="menu" class="mobile-menu" aria-label="Abrir menu" @click="drawerOpen = true" />
            </q-toolbar>
        </q-header>

        <q-drawer v-model="drawerOpen" side="right" overlay bordered class="mobile-drawer">
            <div class="drawer-top">
                <span class="brand">Eco<span>Radar</span></span>
                <q-btn flat round dense icon="close" aria-label="Fechar menu" @click="drawerOpen = false" />
            </div>
            <q-list padding>
                <q-item v-for="item in navigation" :key="item.label" v-ripple clickable :to="item.to" @click="drawerOpen = false">
                    <q-item-section>{{ item.label }}</q-item-section>
                </q-item>
            </q-list>
            <div class="drawer-actions">
                <q-btn outline no-caps label="Entrar" class="full-width" />
                <q-btn unelevated no-caps label="Criar conta" class="primary-button full-width" />
            </div>
        </q-drawer>

        <q-page-container><router-view /></q-page-container>

        <q-footer class="eco-footer">
            <div class="eco-container footer-content">
                <div>
                    <div class="brand brand--footer">
                        <span class="brand__mark"><q-icon name="eco" size="21px" /></span>
                        <span>Eco<span>Radar</span></span>
                    </div>
                    <p>Conectando pessoas a um futuro com menos resíduos e mais consciência.</p>
                </div>
                <div class="footer-links">
                    <RouterLink v-for="item in navigation.slice(0, 4)" :key="item.label" :to="item.to">{{ item.label }}</RouterLink>
                </div>
                <div class="footer-meta">
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
