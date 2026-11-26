<template>
    <div>
        <div class="section-heading section-heading--split">
            <div>
                <span class="section-kicker">
                    Descubra
                </span>
                <h2 :id="headingId">
                    {{ title }}
                </h2>
            </div>
            <slot name="action" />
        </div>
        <div class="materials-grid">
            <MaterialCard
                v-for="material in wasteMaterials"
                :key="material.name"
                :material="material"
                @click="selectedMaterial = material"
            />
        </div>
        <q-dialog
            :model-value="selectedMaterial !== null"
            :aria-labelledby="detailTitleId"
            @update:model-value="selectedMaterial = null"
        >
            <q-card v-if="selectedMaterial" class="material-guide-detail">
                <q-card-section>
                    <div class="flex items-center gap-3">
                        <span
                            class="material-card__icon shrink-0"
                            :style="{ color: selectedMaterial.color, background: selectedMaterial.background }"
                            aria-hidden="true"
                        >
                            <q-icon :name="selectedMaterial.icon" size="27px" />
                        </span>
                        <h2 :id="detailTitleId" class="m-0 text-2xl font-bold leading-tight text-eco-900">
                            {{ selectedMaterial.name }}
                        </h2>
                    </div>
                    <p class="mb-3 mt-5 text-base leading-relaxed text-ink">
                        {{ selectedMaterial.disposalGuide }}
                    </p>
                    <a
                        :href="selectedMaterial.source.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-sm text-eco-700 underline"
                    >
                        Fonte · {{ selectedMaterial.source.name }}
                        <q-icon name="open_in_new" aria-hidden="true" />
                    </a>
                </q-card-section>
                <q-card-actions align="right">
                    <q-btn v-close-popup flat no-caps color="primary" icon="arrow_back" label="Voltar aos materiais" />
                </q-card-actions>
            </q-card>
        </q-dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, useId } from 'vue';
import MaterialCard from './MaterialCard.vue';
import { wasteMaterials } from '../data/home.data';
import type { WasteMaterial } from '../types/home.types';

const selectedMaterial = ref<WasteMaterial | null>(null);
const detailTitleId = useId();

withDefaults(defineProps<{
    title?: string;
    headingId?: string;
}>(), {
    title: 'Guias de descarte',
});
</script>

<style scoped>
.material-guide-detail {
    width: 440px;
    max-width: calc(100vw - 32px);
    border-radius: 20px;
    padding: 8px;
}
</style>
