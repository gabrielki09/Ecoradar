<template>
    <q-page class="home-page">
        <section class="hero-section">
            <div class="hero-blob hero-blob--one"></div>
            <div class="hero-blob hero-blob--two"></div>
            <div class="hero-grid mx-auto w-[min(1180px,calc(100%-40px))] max-[760px]:w-[min(calc(100%-30px),600px)]">
                <div class="hero-copy">
                    <span class="eyebrow"><q-icon name="eco" /> Descarte consciente começa aqui</span>
                    <h1>Seu descarte encontra o <em>lugar certo.</em></h1>
                    <p>Encontre pontos de coleta, agende retiradas e acompanhe o impacto positivo das suas escolhas em
                        Concórdia.</p>
                    <div class="hero-actions">
                        <q-btn unelevated no-caps class="hero-button min-h-[43px] rounded-[11px] bg-eco-600 px-[17px] font-bold text-white hover:bg-eco-700" icon="location_on"
                            label="Encontrar ponto de coleta" href="#pontos" />
                        <q-btn outline no-caps class="secondary-button hero-button" icon="calendar_month"
                            label="Agendar coleta" />
                    </div>
                    <div class="trust-row">
                        <span><q-icon name="verified" /> Pontos verificados</span>
                        <span><q-icon name="schedule" /> Rápido e gratuito</span>
                        <span><q-icon name="favorite" /> Impacto real</span>
                    </div>
                </div>

                <div class="hero-visual" aria-label="Ilustração do ciclo de descarte consciente">
                    <div class="orbit orbit--outer"></div>
                    <div class="orbit orbit--inner"></div>
                    <div class="planet">
                        <div class="planet__land planet__land--one"></div>
                        <div class="planet__land planet__land--two"></div>
                        <div class="planet__land planet__land--three"></div>
                        <q-icon name="eco" />
                    </div>
                    <div class="floating-icon floating-icon--recycle"><q-icon name="recycling" /></div>
                    <div class="floating-icon floating-icon--pin"><q-icon name="location_on" /></div>
                    <div class="floating-icon floating-icon--bottle"><q-icon name="water_drop" /></div>
                    <div class="impact-badge"><q-icon name="trending_up" />
                        <div><strong>+138 kg</strong><span>resíduos destinados</span></div>
                    </div>
                </div>
            </div>
        </section>

        <section id="materiais" class="content-section materials-section">
            <div class="mx-auto w-[min(1180px,calc(100%-40px))] max-[760px]:w-[min(calc(100%-30px),600px)]">
                <div class="section-heading section-heading--split">
                    <div><span class="section-kicker">O que descartar</span>
                        <h2>Encontre o destino de cada material</h2>
                    </div>
                    <a href="#pontos">Ver todos os materiais <q-icon name="arrow_forward" /></a>
                </div>
                <div class="materials-grid">
                    <MaterialCard v-for="material in wasteMaterials" :key="material.name" :material="material" />
                </div>
            </div>
        </section>

        <section id="como-funciona" class="content-section how-section">
            <div class="mx-auto w-[min(1180px,calc(100%-40px))] max-[760px]:w-[min(calc(100%-30px),600px)]">
                <div class="section-heading section-heading--center">
                    <span class="section-kicker">Simples do começo ao fim</span>
                    <h2>Como o EcoRadar funciona</h2>
                    <p>Do resíduo parado em casa ao destino correto, em poucos passos.</p>
                </div>
                <div class="steps-grid">
                    <article v-for="(step, index) in howItWorksSteps" :key="step.number" class="step-card">
                        <div v-if="index < howItWorksSteps.length - 1" class="step-line"></div>
                        <span class="step-number">{{ step.number }}</span>
                        <div class="step-icon"><q-icon :name="step.icon" /></div>
                        <h3>{{ step.title }}</h3>
                        <p>{{ step.description }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="pontos" class="content-section points-section">
            <div class="mx-auto w-[min(1180px,calc(100%-40px))] max-[760px]:w-[min(calc(100%-30px),600px)]">
                <div class="section-heading section-heading--split">
                    <div><span class="section-kicker">Perto de você</span>
                        <h2>Pontos de coleta em destaque</h2>
                        <p>Locais demonstrativos para visualizar a experiência.</p>
                    </div>
                    <q-btn outline no-caps icon="map" label="Explorar no mapa" class="secondary-button" />
                </div>
                <div class="points-grid">
                    <CollectionPointCard v-for="point in collectionPoints" :key="point.id" :point="point" />
                </div>
            </div>
        </section>

        <section class="content-section cta-section">
            <div class="mx-auto w-[min(1180px,calc(100%-40px))] max-[760px]:w-[min(calc(100%-30px),600px)]">
                <div class="cta-card">
                    <div class="cta-card__icon"><q-icon name="volunteer_activism" /></div>
                    <div><span class="section-kicker">Faça parte da mudança</span>
                        <h2>Um descarte de cada vez transforma a cidade.</h2>
                        <p>Crie sua conta para agendar coletas, acompanhar solicitações e visualizar seu impacto.</p>
                    </div>
                    <q-btn unelevated no-caps class="cta-button" label="Começar agora" icon-right="arrow_forward" />
                </div>
            </div>
        </section>
    </q-page>
</template>

<script setup lang="ts">
import CollectionPointCard from '../components/CollectionPointCard.vue';
import MaterialCard from '../components/MaterialCard.vue';
import { collectionPoints, howItWorksSteps, wasteMaterials } from '../data/home.data';
</script>
