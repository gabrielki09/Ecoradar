<template>
    <q-dialog v-model="open" aria-labelledby="materials-guide-title">
        <q-card class="materials-guide-dialog">
            <q-card-section>
                <MaterialsGuide title="Guia de descarte" heading-id="materials-guide-title">
                    <template #action>
                        <q-btn
                            flat
                            round
                            dense
                            icon="close"
                            aria-label="Fechar guia de descarte"
                            @click="open = false"
                        />
                    </template>
                </MaterialsGuide>
            </q-card-section>
        </q-card>
    </q-dialog>
</template>

<script setup lang="ts">
import MaterialsGuide from './MaterialsGuide.vue';

const open = defineModel<boolean>({ required: true });
</script>

<style scoped>
.materials-guide-dialog {
    width: 1100px;
    max-width: calc(100vw - 32px);
    border-radius: 24px;
}

.materials-guide-dialog > .q-card__section {
    padding: clamp(20px, 4vw, 32px);
}

.materials-guide-dialog :deep(.section-heading) {
    flex-direction: row;
    align-items: flex-start;
    gap: 16px;
}
</style>
