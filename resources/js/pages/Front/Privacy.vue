<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import FrontLayout from '@/Layouts/FrontLayout.vue';

defineOptions({
    layout: FrontLayout,
});

const props = defineProps({
    lastUpdated: {
        type: String,
        default: 'September 2026',
    },
    supportEmail: {
        type: String,
        default: 'privacy@prizeguyana.com',
    },
    sections: {
        type: Array,
        required: true,
    },
});

const activeSection = ref(props.sections[0]?.id || 'collection');

const scrollToSection = (id) => {
    activeSection.value = id;
    const element = document.getElementById(id);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};
</script>

<template>
    <Head title="Privacy Policy" />

    <div class="py-6 sm:py-12">
        <!-- Hero Section -->
        <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-semibold uppercase tracking-widest mb-4 shadow-lg shadow-amber-500/5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Data Security & Trust
            </div>
            <h1 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight">
                Privacy <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-amber-500 to-amber-200">Policy</span>
            </h1>
            <p class="mt-4 text-sm sm:text-base text-neutral-400 leading-relaxed">
                Your privacy and security are paramount. This policy details how Prize Guyana collects, protects, and utilizes your personal information. Last updated: {{ lastUpdated }}.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Navigation Sidebar (Sticky on Desktop) -->
            <aside class="lg:col-span-4 sticky top-24 z-10">
                <div class="bg-[#121212] border border-neutral-800/80 rounded-2xl p-5 shadow-2xl backdrop-blur-md">
                    <h3 class="text-xs font-bold text-amber-400 uppercase tracking-widest mb-4 px-2">
                        Sections
                    </h3>
                    <nav class="space-y-1">
                        <button
                            v-for="section in sections"
                            :key="section.id"
                            @click="scrollToSection(section.id)"
                            class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-medium transition-all duration-200 flex items-center justify-between cursor-pointer"
                            :class="activeSection === section.id 
                                ? 'bg-amber-500/15 text-amber-400 font-semibold border border-amber-500/30' 
                                : 'text-neutral-400 hover:text-white hover:bg-neutral-800/50'"
                        >
                            <span>{{ section.title }}</span>
                            <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </nav>

                    <div class="mt-6 pt-5 border-t border-neutral-800/80 px-2 text-center">
                        <p class="text-xs text-neutral-500 mb-3">Privacy Concerns?</p>
                        <a :href="`mailto:${supportEmail}`" class="text-xs text-amber-400 font-semibold hover:underline">
                            {{ supportEmail }}
                        </a>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="lg:col-span-8 space-y-10">
                <section 
                    v-for="(section, index) in sections" 
                    :key="section.id"
                    :id="section.id"
                    class="bg-[#121212] border border-neutral-800/80 rounded-2xl p-6 sm:p-8 shadow-2xl relative overflow-hidden"
                >
                    <div v-if="index === 0" class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-amber-500/50 to-transparent"></div>

                    <h2 class="text-xl sm:text-2xl font-bold text-white mb-4 flex items-center gap-3">
                        <span class="w-2 h-6 bg-amber-500 rounded-full"></span>
                        {{ section.title }}
                    </h2>

                    <div class="text-neutral-300 text-sm leading-relaxed space-y-4">
                        <p v-for="(paragraph, pIndex) in section.content" :key="pIndex">
                            {{ paragraph }}
                        </p>

                        <!-- Bullets List if Present -->
                        <ul v-if="section.bullets" class="list-disc list-inside space-y-2 text-neutral-400 pl-2">
                            <li v-for="(bullet, bIndex) in section.bullets" :key="bIndex">
                                {{ bullet }}
                            </li>
                        </ul>

                        <!-- Contact Highlight Box -->
                        <div v-if="section.id === 'contact'" class="p-4 rounded-xl bg-[#181818] border border-neutral-800 text-sm text-neutral-300 flex items-center gap-4 mt-4">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-white">Privacy Officer</div>
                                <a :href="`mailto:${supportEmail}`" class="text-amber-400 hover:underline">{{ supportEmail }}</a>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>