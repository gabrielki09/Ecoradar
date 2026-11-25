<template>
    <section
        class="w-full max-w-[520px] rounded-[24px] border border-white/80 bg-white/95 p-6 shadow-eco backdrop-blur sm:p-9"
        :aria-labelledby="titleId"
    >
        <div class="mb-7 text-center">
            <span
                class="mb-4 inline-grid size-12 place-items-center rounded-2xl bg-eco-100 text-eco-700"
                aria-hidden="true"
            >
                <q-icon
                    :name="icon"
                    size="27px"
                />
            </span>

            <h1
                :id="titleId"
                class="auth-card-title m-0 font-extrabold leading-tight tracking-[-0.035em] text-eco-900"
            >
                {{ title }}
            </h1>
            <p class="mx-auto mb-0 mt-3 max-w-md text-sm leading-6 text-muted">
                {{ description }}
            </p>
        </div>
        <slot />
    </section>
</template>

<script setup lang="ts">
defineProps<{
    titleId: string;
    icon: string;
    title: string;
    description: string;
}>();
</script>


<style scoped>
.auth-card-title {
    font-size: clamp(1.75rem, 5vw, 2.25rem);
    line-height: 1.2;
    font-weight: 800;
}
</style>
