<script setup>
defineProps({
    updates: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <section class="w-full max-w-7xl mx-auto px-4 py-12 text-white">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Left Column: Title & Subtitle -->
            <div class="lg:col-span-5 pt-4">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-amber-500 tracking-tight">
                    What we've been up to
                </h2>
                <p class="mt-3 text-neutral-400 text-sm sm:text-base font-medium">
                    Latest news, winners, and updates from the team
                </p>
            </div>

            <!-- Right Column: Scrollable Feed Container -->
            <div class="lg:col-span-7">
                <div class="bg-[#0f0e0d] border border-amber-900/30 rounded-2xl p-4 sm:p-5 shadow-2xl">

                    <!-- Scrollable Feed Wrapper -->
                    <div class="max-h-[500px] overflow-y-auto space-y-4 pr-2 custom-scrollbar">

                        <div v-for="(item, index) in updates" :key="`${item.id}-${index}`"
                            class="bg-[#141414] border border-neutral-800/80 rounded-xl p-4 sm:p-5 transition hover:border-neutral-700 flex flex-col sm:flex-row gap-4 sm:gap-6 items-stretch">
                            <!-- Thumbnail Image -->
                            <div
                                class="w-full sm:w-36 h-36 flex-shrink-0 rounded-lg overflow-hidden bg-neutral-900 border border-neutral-800">
                                <img :src="item.image" :alt="item.title"
                                    class="w-full h-full object-cover object-center" />
                            </div>

                            <!-- Content Area -->
                            <div class="flex-1 flex flex-col justify-between py-0.5">

                                <!-- Badge & Time Ago Header -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span
                                            class="bg-amber-500/20 text-amber-400 border border-amber-500/30 text-[11px] font-bold px-3 py-0.5 rounded-md tracking-wide">
                                            {{ item.badge }}
                                        </span>
                                        <span class="text-neutral-500 text-xs font-medium">
                                            {{ item.time_ago }}
                                        </span>
                                    </div>

                                    <!-- Card Title & Subtitle -->
                                    <h3 class="text-lg sm:text-xl font-bold text-white tracking-tight">
                                        {{ item.title }}
                                    </h3>
                                    <p v-if="item.subtitle"
                                        class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mt-0.5">
                                        {{ item.subtitle }}
                                    </p>
                                </div>

                                <!-- Description -->
                                <p
                                    class="text-xs sm:text-sm text-neutral-400 font-normal leading-relaxed mt-2 line-clamp-3">
                                    {{ item.description }}
                                </p>

                            </div>
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