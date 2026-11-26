<template>
    <button type="button" class="material-card" aria-haspopup="dialog" tabindex="0">
        <span
            class="material-card__icon"
            :style="{ color: material.color, background: material.background }"
            aria-hidden="true"
        >
            <q-icon
                :name="material.icon"
                size="27px"
            />
        </span>
        <span>
            <strong class="material-card__name">
                {{ material.name }}
            </strong>
            <span class="material-card__description">
                {{ material.description }}
            </span>
        </span>
        <q-icon
            name="arrow_forward"
            class="material-card__arrow"
            aria-hidden="true"
        />
    </button>
</template>

<script setup lang="ts">
import type { WasteMaterial } from "../types/home.types";

defineProps<{ material: WasteMaterial }>();
</script>
