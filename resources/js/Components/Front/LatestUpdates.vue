<script setup>
defineProps({
    latestWinners: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    
<section class="w-full max-w-7xl mx-auto px-4 py-12 text-white">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Left Column -->
        <div class="lg:col-span-5 pt-4">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-amber-500">
                Celebrating Our Winners
            </span>

            <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                Latest Winners
            </h2>

            <p class="mt-3 text-neutral-400 text-sm sm:text-base font-medium leading-relaxed">
                Real winners. Amazing prizes. Your chance to be next.
            </p>

            <a href="/winners"
                class="inline-flex items-center gap-2 mt-6 text-sm font-bold text-amber-400 hover:text-amber-300 transition">
                View All Winners
                <span>→</span>
            </a>
        </div>

        <!-- Right Column -->
        <div class="lg:col-span-7">
            <div class="bg-[#0f0e0d] border border-amber-900/30 rounded-2xl p-4 sm:p-5 shadow-2xl">

                <div class="max-h-[500px] overflow-y-auto space-y-3 pr-2 custom-scrollbar">

                    <!-- Winner Card -->
                    <div v-for="winner in latestWinners" :key="winner.id"
                        class="bg-[#141414] border border-neutral-800/80 rounded-xl p-4 sm:p-5 flex items-center gap-4 transition hover:border-amber-500/30">

                        <!-- Initials Avatar -->
                        <div
                            class="h-14 w-14 sm:h-16 sm:w-16 shrink-0 rounded-full bg-gradient-to-br from-amber-400/20 to-amber-700/10 border border-amber-500/30 flex items-center justify-center">

                            <span class="text-lg sm:text-xl font-extrabold text-amber-400">
                                {{ winner.initials }}
                            </span>
                        </div>

                        <!-- Winner Information -->
                        <div class="flex-1 min-w-0">

                            <div class="flex items-center justify-between gap-3">
                                <h3 class="text-sm sm:text-base font-bold text-white truncate">
                                    {{ winner.winner_name }}
                                </h3>

                                <span class="text-neutral-500 text-[11px] sm:text-xs whitespace-nowrap">
                                    {{ winner.time_ago }}
                                </span>
                            </div>

                            <p class="mt-1 text-xs text-neutral-500 truncate">
                                {{ winner.competition_title }}
                            </p>

                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-amber-400 text-sm">🏆</span>
                                <p class="text-sm font-bold text-amber-400 truncate">
                                    {{ winner.prize_name || 'Competition Prize' }}
                                </p>
                            </div>

                        </div>

                    </div>

                    <!-- Empty State -->
                    <div v-if="!latestWinners || latestWinners.length === 0"
                        class="py-12 text-center">
                        <div class="text-3xl mb-3">🏆</div>
                        <h3 class="text-white font-bold">
                            Winners Coming Soon
                        </h3>
                        <p class="text-sm text-neutral-500 mt-2">
                            Be part of our next winning story!
                        </p>
                    </div>

                </div>
            </div>
        </div>

    </div>
</section>

</template>

<style scoped>
/* Custom Dark Scrollbar styling */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #141414;
    border-radius: 8px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #2a2a2a;
    border-radius: 8px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #3f3f3f;
}
</style>