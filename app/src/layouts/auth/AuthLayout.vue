<template>
    <q-layout
        view="hHh lpR fFf"
        class="bg-cream text-ink"
    >
        <q-page-container>
            <main
                class="relative min-h-screen overflow-hidden bg-[linear-gradient(135deg,#fbfdf9_0%,#eef8f1_58%,#e3f2e8_100%)]"
            >
                <div
                    aria-hidden="true"
                    class="absolute -left-24 -top-28 size-80 rounded-full bg-eco-100/70 blur-2xl"
                ></div>
                <div
                    aria-hidden="true"
                    class="absolute -bottom-40 -right-32 size-[30rem] rounded-full border-[70px] border-white/35"
                ></div>

                <div
                    class="relative z-10 mx-auto flex min-h-screen w-full max-w-7xl flex-col px-5 py-6 sm:px-8 lg:px-12"
                >
                    <header class="flex items-center justify-between">
                        <RouterLink
                            to="/"
                            class="inline-flex items-center gap-2.5 text-[21px] font-extrabold text-eco-900 no-underline"
                            aria-label="EcoRadar — voltar à página inicial"
                        >
                            <span
                                class="grid size-10 -rotate-3 place-items-center rounded-[12px_12px_12px_4px] bg-eco-600 text-white"
                                aria-hidden="true"
                            >
                                <q-icon
                                    class="rotate-3"
                                    name="eco"
                                    size="24px"
                                />
                            </span>
                            <span>
                                Eco
                                <span class="text-eco-600">
                                    Radar
                                </span>
                            </span>
                        </RouterLink>
                        <RouterLink
                            to="/"
                            class="rounded-lg px-3 py-2 text-sm font-semibold no-underline transition-colors bg-eco-700 text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-eco-600"
                        >
                            Voltar ao início
                        </RouterLink>
                    </header>

                    <div class="flex flex-1 items-center justify-center py-10 sm:py-14">
                        <RouterView />
                    </div>
                </div>
            </main>
        </q-page-container>
    </q-layout>
</template>
