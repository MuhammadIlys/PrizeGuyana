<script setup>
</script>

<template>

    <!-- HOW IT WORKS -->
    <section class="relative overflow-hidden bg-[#0D0D0D] py-16 sm:py-20 mt-5">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <!-- Section Heading -->
            <div class="mb-10">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-extrabold uppercase tracking-wide text-white sm:text-xl">
                        How It Works
                    </h2>
                    <div class="group relative">
                        <svg class="h-4 w-4 cursor-help text-amber-500" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m.08 4h.01" />
                        </svg>

                        <div
                            class="pointer-events-none absolute left-1/2 top-full z-20 mt-2 w-64 -translate-x-1/2 rounded-lg border border-amber-500/20 bg-[#171717] p-3 text-xs leading-5 text-neutral-300 opacity-0 shadow-xl transition group-hover:opacity-100">
                            Follow these simple steps to enter competitions and get a chance to win exciting prizes.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Steps -->
            <div class="relative">

                <!-- Connecting Line -->
                <div
                    class="absolute left-[12.5%] right-[12.5%] top-[39px] hidden h-px bg-gradient-to-r from-amber-500/10 via-amber-500/50 to-amber-500/10 md:block">
                </div>

                <div class="grid grid-cols-1 gap-10 md:grid-cols-4 md:gap-4">

                    <!-- STEP 1 -->
                    <div class="relative flex flex-col items-center text-center md:items-center">
                        <div
                            class="relative z-10 flex h-[80px] w-[80px] items-center justify-center rounded-full border border-amber-500/40 bg-[#0D0D0D] shadow-[0_0_20px_rgba(245,158,11,0.05)]">

                            <!-- Number -->
                            <span
                                class="absolute -left-2 -top-2 flex h-7 w-7 items-center justify-center rounded-full border-2 border-[#0D0D0D] bg-amber-500 text-sm font-extrabold text-black shadow-lg">
                                1
                            </span>

                            <!-- Icon -->
                            <svg class="h-9 w-9 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 7.5 16.5 3l4 11.5L8 19 4 7.5Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m7 5 4 11.5M3.5 10l12.5-4.5M8 19l12.5-4.5" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="m7 5 4 11.5" />
                            </svg>
                        </div>

                        <div class="mt-5 max-w-[210px]">
                            <h3 class="text-xs font-extrabold uppercase tracking-wide text-amber-400 sm:text-sm">
                                Choose Your Competition
                            </h3>
                            <p class="mt-2 text-xs leading-[1.6] text-neutral-400">
                                Browse our competitions and pick the prize you'd love to win.
                            </p>
                        </div>
                    </div>

                    <!-- STEP 2 -->
                    <div class="relative flex flex-col items-center text-center md:items-center">
                        <div
                            class="relative z-10 flex h-[80px] w-[80px] items-center justify-center rounded-full border border-amber-500/40 bg-[#0D0D0D] shadow-[0_0_20px_rgba(245,158,11,0.05)]">

                            <span
                                class="absolute -left-2 -top-2 flex h-7 w-7 items-center justify-center rounded-full border-2 border-[#0D0D0D] bg-amber-500 text-sm font-extrabold text-black shadow-lg">
                                2
                            </span>

                            <svg class="h-9 w-9 text-neutral-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.7">
                                <rect x="2.5" y="5" width="19" height="14" rx="2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 10h19M6 15h3m2 0h2" />
                            </svg>
                        </div>

                        <div class="mt-5 max-w-[210px]">
                            <h3 class="text-xs font-extrabold uppercase tracking-wide text-amber-400 sm:text-sm">
                                Buy Your Tickets
                            </h3>
                            <p class="mt-2 text-xs leading-[1.6] text-neutral-400">
                                Choose your tickets, answer the qualifying question and complete secure checkout.
                            </p>
                        </div>
                    </div>

                    <!-- STEP 3 -->
                    <div class="relative flex flex-col items-center text-center md:items-center">
                        <div
                            class="relative z-10 flex h-[80px] w-[80px] items-center justify-center rounded-full border border-amber-500/40 bg-[#0D0D0D] shadow-[0_0_20px_rgba(245,158,11,0.05)]">

                            <span
                                class="absolute -left-2 -top-2 flex h-7 w-7 items-center justify-center rounded-full border-2 border-[#0D0D0D] bg-amber-500 text-sm font-extrabold text-black shadow-lg">
                                3
                            </span>

                            <svg class="h-9 w-9 text-neutral-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M5 7h14l-1 14H6L5 7Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7a4 4 0 0 1 8 0M9 12h6" />
                            </svg>
                        </div>

                        <div class="mt-5 max-w-[210px]">
                            <h3 class="text-xs font-extrabold uppercase tracking-wide text-amber-400 sm:text-sm">
                                Get Your Tickets
                            </h3>
                            <p class="mt-2 text-xs leading-[1.6] text-neutral-400">
                                Your tickets are assigned after payment. You could even win instantly!
                            </p>
                        </div>
                    </div>

                    <!-- STEP 4 -->
                    <div class="relative flex flex-col items-center text-center md:items-center">
                        <div
                            class="relative z-10 flex h-[80px] w-[80px] items-center justify-center rounded-full border border-amber-500/40 bg-[#0D0D0D] shadow-[0_0_20px_rgba(245,158,11,0.05)]">

                            <span
                                class="absolute -left-2 -top-2 flex h-7 w-7 items-center justify-center rounded-full border-2 border-[#0D0D0D] bg-amber-500 text-sm font-extrabold text-black shadow-lg">
                                4
                            </span>

                            <svg class="h-9 w-9 text-neutral-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 21h8m-4-4v4M7 4h10v5a5 5 0 0 1-10 0V4Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7 6H4v2a4 4 0 0 0 4 4m9-6h3v2a4 4 0 0 1-4 4M10 4V2m4 2V2" />
                            </svg>
                        </div>

                        <div class="mt-5 max-w-[210px]">
                            <h3 class="text-xs font-extrabold uppercase tracking-wide text-amber-400 sm:text-sm">
                                Win & Celebrate
                            </h3>
                            <p class="mt-2 text-xs leading-[1.6] text-neutral-400">
                                Win through Instant Win or the scheduled draw and receive your prize.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

</template>

<style scoped></style>