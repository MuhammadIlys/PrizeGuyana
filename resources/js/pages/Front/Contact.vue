<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import FrontLayout from '../../Layouts/FrontLayout.vue';
import contactHeroImage from '/resources/images/about/hero-right.png';

defineOptions({
    layout: FrontLayout,
});

const props = defineProps({
    appName: String,
    title: {
        type: String,
        default: 'Contact Us',
    },
});

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const submit = () => {
    form.post(route('contact.send'), {
        onSuccess: () => form.reset(),
    });
};

const contactInfo = [
    {
        title: 'Support Email',
        value: 'support@prizeguyana.com',
        description: 'Response time: Within 24 hours',
        icon: '✉️',
    },
    {
        title: 'Live Chat Support',
        value: 'Mon - Sun: 9AM - 10PM',
        description: 'Instant resolution for draw & ticket queries',
        icon: '💬',
    },
    {
        title: 'Headquarters',
        value: 'Georgetown, Guyana',
        description: 'Official registered competition office',
        icon: '📍',
    },
];

const faqs = [
    {
        q: 'How are competition winners drawn?',
        a: 'All our draws are live-streamed using a certified automated random number generator. Winners are announced live on our official channels.',
    },
    {
        q: 'When will I receive my prize if I win?',
        a: 'Cash prizes are processed within 24 to 48 hours. Physical rewards (vehicles, tech) are arranged for collection or direct delivery immediately after verification.',
    },
    {
        q: 'How do I know my ticket entries are confirmed?',
        a: 'Once your payment is complete, your ticket numbers will instantly appear in your Dashboard under "My Tickets" and be emailed to you.',
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
            <div class="absolute top-0 left-0 right-0 h-[1px] bg-gradient-to-r from-transparent via-amber-500/30 to-transparent"></div>

            <div class="max-w-7xl mx-auto z-10 relative">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                    
                    <!-- Left Hero Content -->
                    <div class="lg:col-span-7 text-center lg:text-left">
                        <span class="inline-block text-xs font-extrabold uppercase tracking-widest text-amber-500 bg-amber-500/10 px-3.5 py-1.5 rounded-full border border-amber-500/20 mb-6">
                            Get In Touch
                        </span>
                        
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight text-white leading-[1.15]">
                            We’re Here To <br class="hidden sm:inline" />
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-yellow-500 to-amber-500">
                                Help You Win
                            </span>
                        </h1>
                        
                        <p class="mt-6 text-base sm:text-lg text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                            Have questions about active draws, ticket validation, or claiming a prize? Reach out to our dedicated support team—we’re always ready to assist.
                        </p>
                    </div>

                    <!-- Right Column: Image Frame with Backlight Glow -->
                    <div class="lg:col-span-5 relative">
                        <div class="absolute -inset-1 bg-gradient-to-r from-amber-500/30 to-yellow-500/20 rounded-3xl blur-2xl opacity-75"></div>
                        
                        <div class="relative rounded-3xl p-2.5 shadow-2xl overflow-hidden">
                            <div class="absolute top-0 left-1/4 right-1/4 h-[1px] bg-gradient-to-r from-transparent via-amber-500/40 to-transparent"></div>
                            
                            <img 
                                :src="contactHeroImage" 
                                alt="Customer Support" 
                                class="w-full h-[280px] sm:h-[350px] object-cover rounded-2xl border border-neutral-800/80"
                            />

                            <div class="absolute bottom-6 left-6 bg-[#0a0a0a]/90 backdrop-blur-md border border-amber-500/30 rounded-xl px-4 py-2.5 flex items-center gap-3 shadow-xl">
                                <span class="flex h-3 w-3 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                                </span>
                                <span class="text-xs font-bold text-neutral-200">Support Desk Active</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- CONTACT CARDS GRID -->
        <section class="py-12 bg-[#0d0d0d] border-b border-neutral-800/80 px-4">
            <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
                <div 
                    v-for="(card, i) in contactInfo" 
                    :key="i"
                    class="relative bg-[#121212] border border-neutral-800/80 rounded-2xl p-6 shadow-xl hover:border-amber-500/40 transition group overflow-hidden"
                >
                    <div class="absolute top-0 left-1/4 right-1/4 h-[1px] bg-gradient-to-r from-transparent via-amber-500/30 to-transparent"></div>
                    
                    <div class="text-3xl p-3 bg-[#0a0a0a] rounded-xl border border-neutral-800 inline-block mb-4">
                        {{ card.icon }}
                    </div>
                    <h3 class="text-lg font-bold text-white mb-1">{{ card.title }}</h3>
                    <p class="text-amber-500 font-semibold text-sm sm:text-base mb-1">{{ card.value }}</p>
                    <p class="text-xs text-neutral-400">{{ card.description }}</p>
                </div>
            </div>
        </section>

        <!-- FORM & FAQ SECTION -->
        <section class="py-16 sm:py-24 px-4 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                
                <!-- Contact Form (7 Cols) -->
                <div class="lg:col-span-7 bg-[#121212] border border-neutral-800 rounded-3xl p-6 sm:p-10 shadow-2xl relative">
                    <div class="absolute top-0 left-1/4 right-1/4 h-[1px] bg-gradient-to-r from-transparent via-amber-500/40 to-transparent"></div>

                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-2">
                        Send Us A <span class="text-amber-500">Message</span>
                    </h2>
                    <p class="text-neutral-400 text-sm mb-8">
                        Fill out the details below and our team will get back to you right away.
                    </p>

                    <form @submit.prevent="submit" class="space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <!-- Name -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-neutral-300 mb-2">Your Name</label>
                                <input 
                                    v-model="form.name"
                                    type="text" 
                                    required
                                    placeholder="John Doe" 
                                    class="w-full bg-[#0a0a0a] border border-neutral-800 rounded-xl px-4 py-3.5 text-sm text-white placeholder-neutral-600 focus:outline-none focus:border-amber-500 transition"
                                />
                                <span v-if="form.errors.name" class="text-xs text-red-500 mt-1 block">{{ form.errors.name }}</span>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-neutral-300 mb-2">Email Address</label>
                                <input 
                                    v-model="form.email"
                                    type="email" 
                                    required
                                    placeholder="john@example.com" 
                                    class="w-full bg-[#0a0a0a] border border-neutral-800 rounded-xl px-4 py-3.5 text-sm text-white placeholder-neutral-600 focus:outline-none focus:border-amber-500 transition"
                                />
                                <span v-if="form.errors.email" class="text-xs text-red-500 mt-1 block">{{ form.errors.email }}</span>
                            </div>
                        </div>

                        <!-- Subject -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-300 mb-2">Subject</label>
                            <input 
                                v-model="form.subject"
                                type="text" 
                                required
                                placeholder="Ticket / Draw Query" 
                                class="w-full bg-[#0a0a0a] border border-neutral-800 rounded-xl px-4 py-3.5 text-sm text-white placeholder-neutral-600 focus:outline-none focus:border-amber-500 transition"
                            />
                            <span v-if="form.errors.subject" class="text-xs text-red-500 mt-1 block">{{ form.errors.subject }}</span>
                        </div>

                        <!-- Message -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-300 mb-2">Message</label>
                            <textarea 
                                v-model="form.message"
                                rows="5"
                                required
                                placeholder="Type your message here..." 
                                class="w-full bg-[#0a0a0a] border border-neutral-800 rounded-xl px-4 py-3.5 text-sm text-white placeholder-neutral-600 focus:outline-none focus:border-amber-500 transition"
                            ></textarea>
                            <span v-if="form.errors.message" class="text-xs text-red-500 mt-1 block">{{ form.errors.message }}</span>
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="w-full py-4 bg-gradient-to-r from-amber-400 via-yellow-500 to-amber-500 hover:brightness-110 active:scale-[0.98] text-black font-extrabold text-sm rounded-xl transition-all shadow-xl disabled:opacity-50"
                        >
                            {{ form.processing ? 'Sending Message...' : 'Send Message' }}
                        </button>
                    </form>
                </div>

                <!-- FAQ Sidebar (5 Cols) -->
                <div class="lg:col-span-5 space-y-6">
                    <div>
                        <span class="text-xs font-extrabold uppercase tracking-widest text-amber-500">Quick Answers</span>
                        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight mt-1 mb-6">
                            Frequently Asked Questions
                        </h2>
                    </div>

                    <div class="space-y-4">
                        <div 
                            v-for="(faq, idx) in faqs" 
                            :key="idx"
                            class="bg-[#121212] border border-neutral-800 rounded-2xl p-5 hover:border-neutral-700 transition"
                        >
                            <h3 class="text-base font-bold text-amber-400 mb-2">{{ faq.q }}</h3>
                            <p class="text-xs sm:text-sm text-neutral-300 leading-relaxed">{{ faq.a }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </section>

    </div>
</template>