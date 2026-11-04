<script setup>
import { Head, Link } from '@inertiajs/vue3';
import FrontLayout from '../../Layouts/FrontLayout.vue';
import Stats from '../../Components/Front/Stats.vue';
import heroImage from '/resources/images/about/hero-right.png';

defineOptions({
    layout: FrontLayout,
});

const props = defineProps({
    appName: String,
    status: String,
    title: {
        type: String,
        default: 'About Us',
    },
    stats: {
        type: Array,
        default: () => [],
    },
});

const features = [
    {
        title: 'Guaranteed Winners',
        description: 'Every competition has a fixed draw date and guaranteed winner regardless of ticket sales. No extensions, ever.',
        icon: '🏆',
    },
    {
        title: '100% Transparent Draws',
        description: 'All draws are live-streamed using verified automated random number generators so you can follow along in real-time.',
        icon: '⚡',
    },
    {
        title: 'Giving Back',
        description: 'A portion of ticket proceeds goes directly toward supporting registered local and global charities.',
        icon: '❤️',
    },
];

const steps = [
    {
        number: '01',
        title: 'Choose Your Prize',
        description: 'Browse our active competitions featuring luxury cars, tech, instant cash, and life-changing rewards.',
    },
    {
        number: '02',
        title: 'Grab Your Tickets',
        description: 'Select your lucky numbers or use automated quick pick. Transparent odds on every single competition.',
    },
    {
        number: '03',
        title: 'Watch the Live Draw',
        description: 'Tune in to our live streams on draw day to see if your ticket matches the winning entry.',
    },
];
</script>

<template>

    <Head :title="title" />

    <div class="bg-[#0a0a0a] text-white font-sans overflow-hidden">

        <!-- HERO SECTION -->
       <section 
    class="relative py-16 lg:py-24 px-4 sm:px-6 lg:px-8 border-b border-neutral-800/80 overflow-hidden"
    style="background: radial-gradient(circle at 20% 20%, rgba(130, 65, 10, 0.35) 0%, rgba(20, 15, 10, 0.7) 50%, rgba(10, 10, 10, 1) 100%);"
>
    <!-- Top Divider Glow Line -->
    <div class="absolute top-0 left-0 right-0 h-[1px] bg-gradient-to-r from-transparent via-amber-500/30 to-transparent"></div>

    <div class="max-w-7xl mx-auto z-10 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
            
            <!-- Left Column: Content -->
            <div class="lg:col-span-7 text-center lg:text-left">
                <span class="inline-block text-xs font-extrabold uppercase tracking-widest text-amber-500 bg-amber-500/10 px-3.5 py-1.5 rounded-full border border-amber-500/20 mb-6">
                    Who We Are
                </span>
                
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight text-white leading-[1.15]">
                    Changing Lives, <br class="hidden sm:inline" />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-yellow-500 to-amber-500">
                        One Draw
                    </span> At A Time
                </h1>
                
                <p class="mt-6 text-base sm:text-lg text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    We are the premier prize competition platform built on trust, transparency, and real winners. From luxury vehicles to tax-free cash, we turn dreams into reality every week.
                </p>

                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                    <Link
                        :href="route('home')"
                        class="w-full sm:w-auto px-7 py-3.5 bg-gradient-to-r from-amber-400 via-yellow-500 to-amber-500 hover:brightness-110 active:scale-[0.98] text-black font-extrabold text-sm rounded-xl transition-all shadow-lg border border-yellow-300/30 text-center"
                    >
                        Explore Winners
                    </Link>
                </div>
            </div>

            <!-- Right Column: Image with Ambient Glow -->
            <div class="lg:col-span-5 relative">
                <!-- Background Ambient Backlight Glow -->
                <div class="absolute -inset-1 bg-gradient-to-r from-amber-500/30 to-yellow-500/20 rounded-3xl blur-2xl opacity-75 group-hover:opacity-100 transition duration-1000"></div>
                
                <!-- Image Wrapper Card -->
                <div class="relative rounded-3xl p-2.5  overflow-hidden">
                    <div class="absolute top-0 left-1/4 right-1/4 h-[1px] bg-gradient-to-r from-transparent via-amber-500/40 to-transparent"></div>
                    
                    <img 
                        :src="heroImage" 
                        alt="Prize Winners" 
                        class="w-full h-[320px] sm:h-[400px] object-cover rounded-2xl border border-neutral-800/80"
                    />

                    <!-- Floating Badge Overlay (Optional) -->
                    <div class="absolute bottom-6 left-6 bg-[#0a0a0a]/90 backdrop-blur-md border border-amber-500/30 rounded-xl px-4 py-2.5 flex items-center gap-3 shadow-xl">
                        <span class="flex h-3 w-3 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                        </span>
                        <span class="text-xs font-bold text-neutral-200">Live Draws Every Week</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

        <!-- REUSED STATS SECTION -->
        <Stats :stats="stats" />

        <!-- OUR STORY SECTION -->
        <section class="py-16 sm:py-24 px-4 max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

                <!-- Story Content -->
                <div class="space-y-6">
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
                        Built By Enthusiasts, <br class="hidden sm:inline" />
                        <span class="text-amber-500">Trusted By Thousands</span>
                    </h2>
                    <p class="text-neutral-300 text-sm sm:text-base leading-relaxed">
                        Founded with a simple mission: to make prize competitions fair, transparent, and genuinely
                        exciting. We got tired of hidden fees, drawn-out timer extensions, and unclear odds.
                    </p>
                    <p class="text-neutral-400 text-sm sm:text-base leading-relaxed">
                        When you enter a draw with us, you know exactly how many tickets exist, your exact odds of
                        winning, and the date the winner will be selected—no excuses, no delays.
                    </p>
                    <div class="pt-2">
                        <Link :href="route('home')"
                            class="inline-flex items-center gap-2 px-6 py-3.5 bg-amber-500 hover:bg-amber-400 active:scale-[0.98] text-black font-extrabold text-sm rounded-xl transition-all shadow-lg">
                            Browse Active Competitions
                            <span>&rarr;</span>
                        </Link>
                    </div>
                </div>

                <!-- Feature Cards -->
                <div class="grid grid-cols-1 gap-4">
                    <div v-for="(feature, idx) in features" :key="idx"
                        class="p-6 bg-[#121212]/90 border border-neutral-800 rounded-2xl flex items-start gap-4 hover:border-amber-500/40 transition-colors">
                        <div class="text-3xl p-3 bg-[#0a0a0a] rounded-xl border border-neutral-800">
                            {{ feature.icon }}
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white mb-1">{{ feature.title }}</h3>
                            <p class="text-xs sm:text-sm text-neutral-400 leading-relaxed">{{ feature.description }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- HOW IT WORKS STEPPER -->
        <section class="py-16 bg-[#0d0d0d] border-y border-neutral-800/80 px-4">
            <div class="max-w-6xl mx-auto text-center">
                <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight mb-4">
                    How It Works
                </h2>
                <p class="text-neutral-400 text-sm max-w-xl mx-auto mb-12">
                    Entering our competitions takes less than 2 minutes. Three simple steps stand between you and your
                    next big prize.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-left">
                    <div v-for="(step, i) in steps" :key="i"
                        class="relative bg-[#121212] border border-neutral-800/80 rounded-2xl p-6 shadow-md hover:border-neutral-700 transition">
                        <span class="text-4xl font-black text-amber-500/30 block mb-4">
                            {{ step.number }}
                        </span>
                        <h3 class="text-lg font-bold text-white mb-2">{{ step.title }}</h3>
                        <p class="text-xs sm:text-sm text-neutral-400 leading-relaxed">{{ step.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CALL TO ACTION BANNER -->
        <section class="py-20 px-4 text-center relative overflow-hidden">
            <div
                class="max-w-3xl mx-auto bg-gradient-to-b from-[#141414] to-[#0d0d0d] border border-neutral-800 p-8 sm:p-12 rounded-3xl relative z-10 shadow-2xl">
                <div
                    class="absolute top-0 left-1/4 right-1/4 h-[1px] bg-gradient-to-r from-transparent via-amber-500/50 to-transparent">
                </div>

                <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight mb-4">
                    Ready to be our next winner?
                </h2>
                <p class="text-neutral-300 text-sm sm:text-base mb-8 max-w-lg mx-auto">
                    Take a look at today's active draws and secure your tickets before the timers run out!
                </p>
                <Link :href="route('home')"
                    class="inline-block px-8 py-4 bg-gradient-to-r from-amber-400 via-yellow-500 to-amber-500 hover:brightness-110 active:scale-[0.98] text-black font-extrabold text-sm rounded-xl transition-all shadow-xl">
                    View Active Draws
                </Link>
            </div>
        </section>

    </div>
</template>