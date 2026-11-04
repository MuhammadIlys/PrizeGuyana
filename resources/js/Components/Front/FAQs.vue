<script setup>
import { ref } from 'vue';

const props = defineProps({
    faqs: {
        type: Array,
        default: () => [],
    },
    title: {
        type: String,
        default: 'Frequently Asked Questions',
    },
    subtitle: {
        type: String,
        default: 'Got Questions? We Have Answers',
    },
});

// Track open FAQ accordion index (-1 means all closed, 0 opens first by default)
const openIndex = ref(0);

const toggleFaq = (index) => {
    openIndex.value = openIndex.value === index ? -1 : index;
};
</script>

<template>
    <section class="py-16 sm:py-24 px-4 sm:px-6 lg:px-8 bg-[#0a0a0a] border-t border-neutral-800/80 relative overflow-hidden">
        <!-- Background Glow Accent -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-amber-500/5 blur-[120px] pointer-events-none rounded-full"></div>

        <div class="max-w-4xl mx-auto relative z-10">
            <!-- Header -->
            <div class="text-center mb-12 sm:mb-16">
                <span class="inline-block text-xs font-extrabold uppercase tracking-widest text-amber-500 bg-amber-500/10 px-3.5 py-1.5 rounded-full border border-amber-500/20 mb-4">
                    {{ subtitle }}
                </span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight">
                    {{ title }}
                </h2>
            </div>

            <!-- Accordion List -->
            <div class="space-y-4" v-if="faqs.length > 0 ">
                <div 
                    v-for="(faq, index) in faqs" 
                    :key="faq.id || index"
                    class="bg-[#121212] border rounded-2xl transition-all duration-300 overflow-hidden shadow-lg"
                    :class="openIndex === index ? 'border-amber-500/50 shadow-amber-500/5' : 'border-neutral-800/80 hover:border-neutral-700'"
                >
                    <!-- Question Header (Clickable) -->
                    <button 
                        @click="toggleFaq(index)"
                        type="button"
                        class="w-full cursor-pointer px-6 py-5 flex items-center justify-between text-left focus:outline-none"
                    >
                        <span class="text-base sm:text-lg font-bold text-white pr-4">
                            {{ faq.question }}
                        </span>
                        
                        <!-- Toggle Icon -->
                        <span 
                            class="flex-shrink-0 w-8 h-8 rounded-full  bg-[#0a0a0a] border border-neutral-800 flex items-center justify-center text-amber-500 font-bold transition-transform duration-300"
                            :class="openIndex === index ? 'rotate-180 bg-amber-500/10 border-amber-500/30' : ''"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </button>

                    <!-- Answer Body -->
                    <div 
                        v-show="openIndex === index"
                        class="px-6 pb-6 pt-1 border-t border-neutral-800/50 text-neutral-300 text-sm sm:text-base leading-relaxed transition-all"
                    >
                        {{ faq.answer }}
                    </div>
                </div>
            </div>

            <!-- Empty State Fallback -->
            <div v-else class="text-center py-8 text-neutral-500 text-sm">
                No questions available at the moment.
            </div>
        </div>
    </section>
</template>