<template>
    <q-page
        class="bg-[linear-gradient(135deg,#fbfdf9_0%,#eef8f1_62%,#e4f3e8_100%)] px-4 py-9 sm:px-6 sm:py-14"
    >
        <main class="mx-auto w-full max-w-[860px]">
            <div class="mb-7 flex items-center gap-3 text-sm font-semibold text-eco-700">
                <span class="grid size-9 place-items-center rounded-full bg-eco-600 text-white">
                    1
                </span>
                <span class="h-px flex-1 bg-eco-200"></span>
                <span
                    class="grid size-9 place-items-center rounded-full border-2 border-eco-600 bg-white text-eco-700"
                >
                    2
                </span>
                <span class="h-px flex-1 bg-line"></span>
                <span
                    class="grid size-9 place-items-center rounded-full border border-line bg-white text-muted"
                >
                    3
                </span>
            </div>

            <section
                class="min-w-0 rounded-[24px] border border-white/80 bg-white/95 p-5 shadow-eco sm:p-9"
                :aria-labelledby="titleId"
            >
                <header class="mb-8 min-w-0">
                    <span
                        class="mb-4 inline-grid size-12 place-items-center rounded-2xl bg-eco-100 text-eco-700"
                    >
                        <q-icon
                            :name="icon"
                            size="27px"
                        />
                    </span>
                    <p class="mb-2 text-xs font-extrabold uppercase tracking-[0.14em] text-eco-600">
                        Complete seu perfil
                    </p>
                    <h1
                        :id="titleId"
                        class="onboarding-title m-0 max-w-full tracking-[-0.04em] text-eco-900"
                    >
                        {{ title }}
                    </h1>
                    <p class="mb-0 mt-3 max-w-2xl text-sm leading-6 text-muted">
                        {{ description }}
                    </p>
                </header>
                <slot />
            </section>
        </main>
    </q-page>
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
.onboarding-title {
    font-size: clamp(1.75rem, 5vw, 2.5rem);
    line-height: 1.2;
    font-weight: 800;
    overflow-wrap: anywhere;
}
</style>
