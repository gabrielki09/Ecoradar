<template>
    <q-page class="home-page">
        <section class="hero-section">
            <div class="hero-blob hero-blob--one"></div>
            <div class="hero-blob hero-blob--two"></div>
            <div class="eco-container hero-grid">
                <div class="hero-copy">
                    <span class="eyebrow">
                        <q-icon name="eco" />
                        Descarte consciente começa aqui
                    </span>
                    <h1>
                        Seu descarte encontra o
                        <em>
                            lugar certo.
                        </em>
                    </h1>
                    <p>
                        Encontre pontos de coleta, solicite a retirada de resíduos e acompanhe seus pedidos.
                    </p>
                    <div class="hero-actions">
                        <q-btn
                            unelevated
                            no-caps
                            class="primary-button hero-button"
                            icon="location_on"
                            label="Encontrar ponto de coleta"
                            :to="{ name: 'home', hash: '#pontos' }"
                        />
                        <q-btn
                            v-if="!auth.isAuthenticated || auth.canRequest"
                            outline
                            no-caps
                            class="secondary-button hero-button"
                            icon="calendar_month"
                            label="Solicitar coleta"
                            :to="{ name: 'collection-create' }"
                        />
                    </div>
                </div>

                <div
                    class="hero-visual"
                    aria-label="Ilustração do ciclo de descarte consciente"
                >
                    <div class="orbit orbit--outer"></div>
                    <div class="orbit orbit--inner"></div>
                    <div class="planet">
                        <div class="planet__land planet__land--one"></div>
                        <div class="planet__land planet__land--two"></div>
                        <div class="planet__land planet__land--three"></div>
                        <q-icon name="eco" />
                    </div>
                    <div class="floating-icon floating-icon--recycle">
                        <q-icon name="recycling" />
                    </div>
                    <div class="floating-icon floating-icon--pin">
                        <q-icon name="location_on" />
                    </div>
                    <div class="floating-icon floating-icon--bottle">
                        <q-icon name="water_drop" />
                    </div>
                </div>
            </div>
        </section>

        <section
            id="materiais"
            class="content-section materials-section"
        >
            <div class="eco-container">
                <MaterialsGuide />
            </div>
        </section>

        <section
            id="como-funciona"
            class="content-section how-section"
        >
            <div class="eco-container">
                <div class="section-heading section-heading--center">
                    <span class="section-kicker">
                        Simples do começo ao fim
                    </span>
                    <h2>
                        Como o EcoRadar funciona
                    </h2>
                    <p>
                        Do resíduo parado em casa ao destino correto, em poucos passos.
                    </p>
                </div>
                <div class="steps-grid">
                    <article
                        v-for="(step, index) in howItWorksSteps"
                        :key="step.number"
                        class="step-card"
                    >
                        <div
                            v-if="index < howItWorksSteps.length - 1"
                            class="step-line"
                        ></div>
                        <span class="step-number">
                            {{ step.number }}
                        </span>
                        <div class="step-icon"><q-icon :name="step.icon" /></div>
                        <h3>
                            {{ step.title }}
                        </h3>
                        <p>
                            {{ step.description }}
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section
            id="pontos"
            class="content-section points-section"
        >
            <div class="eco-container">
                <div class="section-heading section-heading--split">
                    <div>
                        <span class="section-kicker">
                            Perto de você
                        </span>
                        <h2>Pontos de coleta</h2>
                        <p>Encontre um local para levar seus resíduos.</p>
                    </div>
                    <RouterLink
                        v-if="points.length"
                        :to="{ name: 'home', hash: '#lista-pontos' }"
                        class="points-list-link"
                    >
                        Consultar lista de pontos
                    </RouterLink>
                </div>
                <div v-if="loading" role="status" class="points-state"><q-spinner color="primary" /> Carregando pontos…</div>
                <div v-else-if="error" role="alert" class="points-state">
                    {{ error }} <q-btn flat no-caps label="Tentar novamente" @click="load" />
                </div>
                <p v-else-if="!points.length" class="points-state">Nenhum ponto público disponível no momento.</p>
                <template v-else>
                    <p v-if="!mappedPoints.length" class="points-state">Os pontos cadastrados ainda não possuem coordenadas. Consulte os endereços na lista abaixo.</p>
                    <div v-else class="points-map-layout">
                        <PointsMap :points="mappedPoints" :selected-id="selectedId" @select="selectedId = $event" />
                        <div v-if="selected" class="points-selection" aria-live="polite">
                            <CollectionPointCard :point="selected" always-expanded />
                        </div>
                        <p v-else class="points-selection points-state">Selecione um marcador ou um ponto na lista para ver os detalhes.</p>
                    </div>
                    <p v-if="mappedPoints.length < points.length" class="points-state">Alguns pontos ainda não têm coordenadas e aparecem somente na lista.</p>
                </template>
                <div v-if="points.length" id="lista-pontos" class="points-grid" aria-label="Lista de pontos de coleta">
                    <CollectionPointCard
                        v-for="point in points"
                        :key="point.id"
                        :point="point"
                        :selected="selectedId === point.id"
                        @select="selectedId = point.id"
                    />
                </div>
            </div>
        </section>

        <section v-if="!auth.isAuthenticated" class="content-section cta-section">
            <div class="eco-container">
                <div class="cta-card">
                    <div class="cta-card__icon"><q-icon name="volunteer_activism" /></div>
                    <div>
                        <span class="section-kicker">
                            Faça parte da mudança
                        </span>
                        <h2>
                            Um descarte de cada vez transforma a cidade.
                        </h2>
                        <p>
                            Crie sua conta para solicitar coletas e acompanhar seus pedidos.
                        </p>
                    </div>
                    <q-btn
                        unelevated
                        no-caps
                        class="cta-button"
                        label="Começar agora"
                        icon-right="arrow_forward"
                        to="/cadastro"
                    />
                </div>
            </div>
        </section>
    </q-page>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/modules/auth/stores/auth.store';
import { computed, onMounted, ref } from 'vue';
import CollectionPointCard from "../components/CollectionPointCard.vue";
import MaterialsGuide from "../components/MaterialsGuide.vue";
import PointsMap from '../components/PointsMap.vue';
import { howItWorksSteps } from "../data/home.data";
import { loadPublicPoints } from '../services/public-points.service';
import type { PublicPoint } from '../types/public-point.types';

const auth = useAuthStore();
const points = ref<PublicPoint[]>([]);
const selectedId = ref<number | null>(null);
const loading = ref(true);
const error = ref('');
const selected = computed(() => points.value.find(point => point.id === selectedId.value));
const mappedPoints = computed(() => points.value.filter(point =>
    typeof point.latitude === 'number' && typeof point.longitude === 'number' &&
    Number.isFinite(point.latitude) && Number.isFinite(point.longitude) &&
    Math.abs(point.latitude) <= 90 && Math.abs(point.longitude) <= 180,
));

async function load() {
    loading.value = true;
    error.value = '';
    try {
        points.value = await loadPublicPoints();
        if (!points.value.some(point => point.id === selectedId.value)) selectedId.value = null;
    } catch {
        points.value = [];
        error.value = 'Não foi possível carregar os pontos de coleta.';
    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>
