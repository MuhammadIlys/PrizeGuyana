<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { formatDate } from '../../helpers/date'
import { formatMoney } from '@/helpers/money.js';


defineOptions({ layout: FrontLayout });

const props = defineProps({
    competition: { type: Object, required: true },
    prizes: { type: Array, default: () => [] },
    terms: { type: Object, default: null },
    qualificationQuestions: { type: Array, default: () => [] },
    countries: { type: Array, default: () => [] },
});

const quantity = ref(1);
const activeTab = ref('prizes');
const addingToCart = ref(false);
const cartError = ref('');

const showQualificationModal = ref(false);
const modalQualificationQuestions = ref([]);
const qualificationAnswers = ref({});
const qualificationError = ref('');
const submittingQualification = ref(false);

const currency = computed(() => props.competition.currency || 'USD');
const entryPrice = computed(() => Number(props.competition.entry_price || 0));
const totalPrice = computed(() => entryPrice.value * quantity.value);

const maxQuantity = computed(() => {
    const limit = Number(props.competition.max_entries_per_customer || 0);
    const total = Number(props.competition.total_entries || 0);

    return Math.max(1, limit || Math.min(total || 1, 100));
});

const mainPrize = computed(() =>
    props.prizes.find(p => p.prize_role === 'main') ||
    props.prizes[0] ||
    null
);

const imageUrl = computed(() => {
    const image = props.competition.main_image || mainPrize.value?.image_path;

    if (!image) return null;
    if (/^https?:\/\//i.test(image) || image.startsWith('/')) return image;

    return `/storage/${image}`;
});

const isOpen = computed(() => {
    const now = new Date();
    const start = props.competition.starts_at
        ? new Date(props.competition.starts_at)
        : null;
    const end = props.competition.ends_at
        ? new Date(props.competition.ends_at)
        : null;

    return ['published', 'active'].includes(props.competition.status)
        && (!start || start <= now)
        && (!end || end > now);
});



function clampQuantity(value) {
    quantity.value = Math.max(
        1,
        Math.min(maxQuantity.value, Math.floor(Number(value) || 1))
    );
}

function decreaseQuantity() {
    clampQuantity(quantity.value - 1);
}

function increaseQuantity() {
    clampQuantity(quantity.value + 1);
}

function getCountryLabel(country) {
    if (typeof country === 'string') return country;

    return country.name ||
        country.country_name ||
        country.country_code ||
        country.code ||
        '';
}

function getPrizeImage(prize) {
    const image = prize.image_path || prize.image;

    if (!image) return null;
    if (/^https?:\/\//i.test(image) || image.startsWith('/')) return image;

    return `/storage/${image}`;
}

function addToCart() {
    if (!isOpen.value || addingToCart.value) return;

    cartError.value = '';
    addingToCart.value = true;

    router.post(route('cart.add'), {
        competition_id: props.competition.id,
        quantity: quantity.value,
    }, {
        preserveScroll: true,
        onError: errors => {
            cartError.value = Object.values(errors).flat().join(' ');
        },
        onFinish: () => {
            addingToCart.value = false;
        },
    });





}

async function enterCompetition() {
    if (addingToCart.value || !isOpen.value) return;

    addingToCart.value = true;
    cartError.value = '';

    try {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');

        if (!csrfToken) {
            throw new Error('CSRF token not found. Check your Blade layout.');
        }

        const response = await fetch(
            route('competition.eligibility', props.competition.id),
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    quantity: quantity.value,
                }),
            }
        );

        const data = await response.json();

        if (!response.ok || !data.success) {
            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            }

            cartError.value =
                data.message ||
                'You are not eligible to enter this competition.';

            return;
        }

        if (data.requires_qualification) {
            console.log('Qualification questions:', data.qualification_questions);
            modalQualificationQuestions.value = data.qualification_questions || [];
            qualificationAnswers.value = {};
            qualificationError.value = '';

            showQualificationModal.value = true;
            return;
        }

        // Add to cart after eligibility verification.
        router.post(
            route('cart.add'),
            {
                competition_id: props.competition.id,
                quantity: quantity.value,
            },
            {
                onError: (errors) => {
                    cartError.value =
                        errors.message ||
                        'Unable to add this competition to your cart.';
                },
                onFinish: () => {
                    addingToCart.value = false;
                },
            }
        );

        // Inertia now handles the loading state.
        return;

    } catch (error) {
        console.error('Competition eligibility error:', error);

        cartError.value =
            error.message || 'Unable to verify eligibility. Please try again.';

        addingToCart.value = false;
    }

    addingToCart.value = false;
}

async function submitQualification() {
    console.log('Submit qualification clicked');
    if (submittingQualification.value) return;

    qualificationError.value = '';

    // Ensure every question has an answer.
    const unanswered = modalQualificationQuestions.value.some(
        question => !qualificationAnswers.value[question.id]
    );

    if (unanswered) {
        qualificationError.value = 'Please answer all qualification questions.';
        return;
    }

    submittingQualification.value = true;

    try {
        const response = await fetch(
            route('competition.qualification', props.competition.id),
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document.querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') ?? '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    answers: qualificationAnswers.value,
                    quantity: quantity.value,
                }),
            }
        );

        const data = await response.json();

        if (!response.ok || !data.success) {
            qualificationError.value =
                data.message || 'Your answers could not be verified.';
            return;
        }

        // All answers are correct. Add the competition to the cart.
        router.post(
            route('cart.add'),
            {
                competition_id: props.competition.id,
                quantity: quantity.value,
            },
            {
                onError: (errors) => {
                    qualificationError.value =
                        errors.message || 'Unable to add the competition to your cart.';
                },
                onSuccess: () => {
                    showQualificationModal.value = false;
                },
                onFinish: () => {
                    submittingQualification.value = false;
                },
            }
        );

        return;
    } catch (error) {
        console.error('Qualification submission error:', error);

        qualificationError.value =
            'Unable to verify your answers. Please try again.';
    } finally {
        submittingQualification.value = false;
    }
}

</script>

<template>

    <Head :title="`${competition.title} | PrizeGuyana`" />

    <div class="min-h-screen bg-[#0c0c0c] text-white">
        <div class="h-1 bg-gradient-to-r from-[#82410a] via-[#eec76f] to-amber-500"></div>

        <!-- Breadcrumb -->
        <div class="border-b border-white/10 bg-[#111111]">
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                <nav class="flex flex-wrap items-center gap-2 text-sm">
                    <Link href="/" class="text-white/55 hover:text-[#eec76f]">
                        Home
                    </Link>
                    <span class="text-white/25">/</span>
                    <span class="text-white/55">Competitions</span>
                    <span class="text-white/25">/</span>
                    <span class="max-w-xs truncate font-semibold">
                        {{ competition.title }}
                    </span>
                </nav>
            </div>
        </div>

        <main class="mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-10 lg:px-8">
            <div class="grid items-start gap-7 lg:grid-cols-12 lg:gap-9">

                <!-- LEFT: Competition details -->
                <section class="space-y-6 lg:col-span-7">

                    <!-- Hero -->
                    <div
                        class="overflow-hidden rounded-2xl border border-white/10 bg-[#161616] shadow-2xl shadow-black/30">
                        <div class="relative aspect-[4/3] overflow-hidden bg-black">
                            <img v-if="imageUrl" :src="imageUrl" :alt="competition.title"
                                class="h-full w-full object-cover" />

                            <div v-else class="flex h-full flex-col items-center justify-center text-white/50">
                                <span class="text-6xl">🎁</span>
                                <p class="mt-3 text-sm">Prize image coming soon</p>
                            </div>

                            <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/20 to-transparent">
                            </div>

                            <div class="absolute inset-x-0 bottom-0 p-5 sm:p-7">
                                <div class="mb-3 flex flex-wrap gap-2">
                                    <span v-if="competition.is_featured"
                                        class="rounded-full bg-gradient-to-r from-amber-400 to-amber-500 px-3 py-1.5 text-xs font-black uppercase text-black">
                                        ★ Featured
                                    </span>

                                    <span class="rounded-full border px-3 py-1.5 text-xs font-bold" :class="isOpen
                                        ? 'border-[#eec76f]/40 bg-black/50 text-[#f5d990]'
                                        : 'border-white/15 bg-black/50 text-white/65'">
                                        {{ isOpen ? 'Entries open' : 'Entries unavailable' }}
                                    </span>
                                </div>

                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#eec76f]">
                                    {{ competition.category_name || 'PrizeGuyana competition' }}
                                </p>

                                <h1 class="mt-2 text-2xl font-black leading-tight sm:text-4xl">
                                    {{ competition.title }}
                                </h1>

                                <p v-if="competition.short_description"
                                    class="mt-3 max-w-2xl text-sm leading-6 text-white/70 sm:text-base">
                                    {{ competition.short_description }}
                                </p>
                            </div>
                        </div>

                        <!-- Key information -->
                        <div class="grid grid-cols-2 divide-x divide-white/10 sm:grid-cols-3">
                            <div class="p-4 sm:p-5">
                                <p class="text-xs font-semibold uppercase tracking-wider text-white/45">
                                    Entry price
                                </p>
                                <p class="mt-1.5 text-xl font-black text-[#eec76f]">
                                    {{ formatMoney(entryPrice) }}
                                </p>
                            </div>

                            <div class="p-4 sm:p-5">
                                <p class="text-xs font-semibold uppercase tracking-wider text-white/45">
                                    Draw date
                                </p>
                                <p class="mt-1.5 text-sm font-bold text-white/90">
                                    {{ formatDate(competition.draw_at) }}
                                </p>
                            </div>

                            <div class="col-span-2 border-t border-white/10 p-4 sm:col-span-1 sm:border-t-0 sm:p-5">
                                <p class="text-xs font-semibold uppercase tracking-wider text-white/45">
                                    Minimum age
                                </p>
                                <p class="mt-1.5 text-sm font-bold text-white/90">
                                    {{ competition.minimum_age || 18 }}+
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Full description -->
                    <div class="rounded-2xl border border-white/10 bg-[#151515] p-5 shadow-xl sm:p-7">
                        <div class="flex items-center gap-3">
                            <span class="h-8 w-1 rounded-full bg-gradient-to-b from-[#eec76f] to-amber-600"></span>
                            <h2 class="text-xl font-black">Competition overview</h2>
                        </div>

                        <p v-if="competition.description"
                            class="mt-4 whitespace-pre-line text-sm leading-7 text-white/65">
                            {{ competition.description }}
                        </p>

                        <p v-else-if="competition.short_description" class="mt-4 text-sm leading-7 text-white/65">
                            {{ competition.short_description }}
                        </p>

                        <p v-else class="mt-4 text-sm leading-7 text-white/45">
                            More information about this competition will be available soon.
                        </p>
                    </div>

                    <!-- Prizes, questions and terms -->
                    <div class="overflow-hidden rounded-2xl border border-white/10 bg-[#151515] shadow-xl">
                        <div class="flex overflow-x-auto border-b border-white/10 bg-[#101010]">
                            <button v-for="tab in [
                                { id: 'prizes', label: 'Prizes' },
                                { id: 'questions', label: 'Qualification Questions' },
                                { id: 'terms', label: 'Terms & Conditions' }
                            ]" :key="tab.id" type="button"
                                class="shrink-0 border-b-2 px-4 py-4 text-sm font-bold transition sm:px-6" :class="activeTab === tab.id
                                    ? 'border-[#eec76f] text-[#eec76f]'
                                    : 'border-transparent text-white/45 hover:text-white'" @click="activeTab = tab.id">
                                {{ tab.label }}
                            </button>
                        </div>

                        <div class="p-5 sm:p-6">

                            <!-- Prizes -->
                            <div v-if="activeTab === 'prizes'">
                                <div v-if="prizes.length" class="space-y-4">
                                    <article v-for="(prize, index) in prizes" :key="prize.id || index"
                                        class="flex flex-col gap-4 rounded-xl border border-white/10 bg-black/20 p-4 sm:flex-row">
                                        <div
                                            class="h-24 w-full shrink-0 overflow-hidden rounded-lg border border-white/10 bg-[#0c0c0c] sm:w-28">
                                            <img v-if="getPrizeImage(prize)" :src="getPrizeImage(prize)"
                                                :alt="prize.title || prize.name || 'Competition prize'"
                                                class="h-full w-full object-cover" />
                                            <div v-else class="flex h-full items-center justify-center text-3xl">
                                                🏆
                                            </div>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-start justify-between gap-2">
                                                <h3 class="font-bold text-white">
                                                    {{ prize.title || prize.name || `Prize ${index + 1}` }}
                                                </h3>

                                                <span v-if="prize.prize_role"
                                                    class="rounded-full border border-[#eec76f]/20 bg-[#eec76f]/10 px-2.5 py-1 text-[10px] font-bold uppercase text-[#eec76f]">
                                                    {{ prize.prize_role.replaceAll('_', ' ') }}
                                                </span>
                                            </div>

                                            <p v-if="prize.description" class="mt-2 text-sm leading-6 text-white/55">
                                                {{ prize.description }}
                                            </p>

                                            <p v-if="prize.value !== undefined && prize.value !== null"
                                                class="mt-3 text-sm font-extrabold text-[#eec76f]">
                                                Prize value:
                                                {{ formatMoney(prize.value, prize.currency || currency) }}
                                            </p>

                                            <p v-else-if="prize.prize_value !== undefined && prize.prize_value !== null"
                                                class="mt-3 text-sm font-extrabold text-[#eec76f]">
                                                Prize value:
                                                {{ formatMoney(prize.prize_value, prize.currency || currency) }}
                                            </p>
                                        </div>
                                    </article>
                                </div>

                                <p v-else class="py-8 text-center text-sm text-white/45">
                                    Prize details will be announced soon.
                                </p>
                            </div>

                            <!-- Questions only: no answer inputs -->
                            <div v-else-if="activeTab === 'questions'">
                                <div v-if="competition.qualification_enabled">
                                    <p class="mb-5 text-sm leading-6 text-white/55">
                                        These questions must be answered correctly when entering this competition.
                                    </p>
                                    <div v-if="props.qualificationQuestions.length" class="space-y-4">
                                        <div v-for="(question, index) in props.qualificationQuestions"
                                            :key="question.id || index"
                                            class="rounded-xl border border-white/10 bg-black/20 p-4">
                                            <div class="flex items-start gap-3">
                                                <span
                                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-[#eec76f]/20 bg-[#eec76f]/10 text-sm font-black text-[#eec76f]">
                                                    {{ index + 1 }}
                                                </span>

                                                <p class="pt-1 text-sm font-semibold leading-6 text-white/90">
                                                    {{ question.question || question.title || question.question_text ||
                                                        'Qualification question' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <p v-else
                                        class="rounded-xl border border-amber-400/20 bg-amber-950/20 p-4 text-sm text-amber-100/70">
                                        Qualification questions are not available yet.
                                    </p>
                                </div>

                                <p v-else class="py-8 text-center text-sm text-white/45">
                                    No qualification questions are required for this competition.
                                </p>
                            </div>

                            <!-- Terms and conditions -->
                            <div v-else-if="activeTab === 'terms'">
                                <template v-if="terms">
                                    <h3 class="text-lg font-black text-white">
                                        {{ terms.title || 'Terms & Conditions' }}
                                    </h3>

                                    <p class="mt-2 text-xs text-white/40">
                                        <span v-if="terms.version">Version {{ terms.version }}</span>
                                        <span v-if="terms.version && terms.updated_at"> · </span>
                                        <span v-if="terms.updated_at">
                                            Updated {{ formatDate(terms.updated_at) }}
                                        </span>
                                    </p>

                                    <div class="mt-5 whitespace-pre-line text-sm leading-7 text-white/65">
                                        {{ terms.content || terms.terms || 'Terms and conditions will be available soon.' }}
                                    </div>
                                </template>

                                <p v-else class="py-8 text-center text-sm text-white/45">
                                    Terms and conditions will be available soon.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- RIGHT: Entry panel and competition metadata -->
                <aside class="space-y-6 lg:col-span-5">
                    <div class="lg:sticky lg:top-6 lg:space-y-6">

                        <!-- Entry purchase card -->
                        <div
                            class="overflow-hidden rounded-2xl border border-[#eec76f]/20 bg-[#161616] shadow-2xl shadow-black/30">
                            <div
                                class="border-b border-white/10 bg-gradient-to-r from-[#82410a]/40 via-[#21180e] to-[#161616] p-5 sm:p-6">
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#eec76f]">
                                    Enter competition
                                </p>

                                <h2 class="mt-2 text-2xl font-black">
                                    {{ formatMoney(entryPrice) }}
                                    <span class="text-sm font-medium text-white/45">/ entry</span>
                                </h2>

                                <p class="mt-2 text-sm text-white/50">
                                    Choose your entry quantity to see your total.
                                </p>
                            </div>

                            <div class="p-5 sm:p-6">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm text-white/55">Competition status</span>

                                    <span class="rounded-full px-3 py-1 text-xs font-bold" :class="isOpen
                                        ? 'bg-emerald-400/10 text-emerald-300'
                                        : 'bg-white/5 text-white/45'">
                                        {{ isOpen ? '● Entries open' : 'Entries unavailable' }}
                                    </span>
                                </div>

                                <div class="my-6 border-t border-white/10"></div>

                                <div class="space-y-4">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/5 text-[#eec76f]">◷</span>
                                        <div>
                                            <p class="text-xs text-white/40">Entry deadline</p>
                                            <p class="mt-0.5 text-sm font-bold text-white/85">
                                                {{ formatDate(competition.ends_at) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <span
                                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/5 text-[#eec76f]">🏆</span>
                                        <div>
                                            <p class="text-xs text-white/40">Draw date</p>
                                            <p class="mt-0.5 text-sm font-bold text-white/85">
                                                {{ formatDate(competition.draw_at) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quantity selector -->
                                <div class="mt-7">
                                    <div class="flex items-center justify-between gap-3">
                                        <label for="entry-quantity" class="text-sm font-bold text-white/90">
                                            Number of entries
                                        </label>
                                        <span class="text-xs text-white/40">
                                            Maximum {{ maxQuantity }}
                                        </span>
                                    </div>

                                    <div
                                        class="mt-3 flex items-center justify-between rounded-xl border border-white/15 bg-black/25 p-2 focus-within:border-[#eec76f]/70">
                                        <button type="button" aria-label="Decrease quantity"
                                            :disabled="quantity <= 1 || !isOpen"
                                            class="flex h-11 w-11 items-center justify-center rounded-lg text-2xl transition hover:bg-white/10 disabled:opacity-25"
                                            @click="decreaseQuantity">
                                            −
                                        </button>

                                        <input id="entry-quantity" :value="quantity" type="number" min="1"
                                            :max="maxQuantity" :disabled="!isOpen"
                                            class="w-20 border-0 bg-transparent text-center text-xl font-black text-white focus:ring-0 disabled:opacity-50"
                                            @input="clampQuantity($event.target.value)" />

                                        <button type="button" aria-label="Increase quantity"
                                            :disabled="quantity >= maxQuantity || !isOpen"
                                            class="flex h-11 w-11 items-center justify-center rounded-lg text-2xl transition hover:bg-white/10 disabled:opacity-25"
                                            @click="increaseQuantity">
                                            +
                                        </button>
                                    </div>

                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <button v-for="amount in [1, 5, 10]" :key="amount" type="button"
                                            :disabled="amount > maxQuantity || !isOpen"
                                            class="rounded-lg border px-3 py-2 text-xs font-bold transition disabled:opacity-25"
                                            :class="quantity === amount
                                                ? 'border-[#eec76f] bg-[#eec76f] text-[#17120a]'
                                                : 'border-white/15 text-white/60 hover:border-[#eec76f]/60 hover:text-[#eec76f]'"
                                            @click="clampQuantity(amount)">
                                            {{ amount }} {{ amount === 1 ? 'entry' : 'entries' }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Price summary -->
                                <div class="mt-6 rounded-xl border border-white/10 bg-black/30 p-4">
                                    <div class="flex items-center justify-between text-sm text-white/45">
                                        <span>{{ quantity }} × {{ formatMoney(entryPrice) }}</span>
                                        <span>{{ formatMoney(totalPrice) }}</span>
                                    </div>

                                    <div class="mt-3 flex items-center justify-between border-t border-white/10 pt-3">
                                        <span class="font-bold text-white">Total</span>
                                        <span class="text-2xl font-black text-[#eec76f]">
                                            {{ formatMoney(totalPrice) }}
                                        </span>
                                    </div>
                                </div>


                                <button type="button" :disabled="!isOpen || addingToCart"
                                    class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-400 via-[#eec76f] to-amber-500 px-5 py-4 text-base font-black text-[#17120a] shadow-lg shadow-amber-950/30 transition hover:brightness-110 focus:outline-none focus:ring-4 focus:ring-[#eec76f]/25 active:scale-[0.99] disabled:cursor-not-allowed disabled:from-slate-700 disabled:via-slate-700 disabled:to-slate-700 disabled:text-white/40 disabled:shadow-none"
                                    @click="enterCompetition">
                                    <span v-if="addingToCart">Checking eligibility...</span>

                                    <template v-else>
                                        <span>{{ isOpen ? 'Enter now' : 'Entries unavailable' }}</span>
                                        <span v-if="isOpen">{{ formatMoney(totalPrice) }}</span>
                                    </template>
                                </button>

                                <!-- <p v-if="cartError" class="mt-3 text-sm text-red-400">
                                    {{ cartError }}
                                </p> -->


                                <div v-if="cartError" role="alert"
                                    class="mt-4 rounded-xl border border-red-400/20 bg-red-950/40 p-3 text-sm text-red-200">
                                    {{ cartError }}
                                </div>

                                <div
                                    class="mt-5 flex items-start gap-3 rounded-xl border border-white/10 bg-black/20 p-4">
                                    <span class="mt-0.5 text-lg text-[#eec76f]">🔒</span>
                                    <div>
                                        <p class="text-sm font-bold text-white/90">Secure entry</p>
                                        <p class="mt-1 text-xs leading-5 text-white/45">
                                            Eligibility and entry availability will be validated by the server before an
                                            order is
                                            accepted.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Competition metadata -->
                        <div class="rounded-2xl border border-white/10 bg-[#151515] p-5 shadow-xl sm:p-6">
                            <h2 class="text-base font-black">Competition information</h2>

                            <dl class="mt-3 divide-y divide-white/10">
                                <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-sm text-white/45">Category</dt>
                                    <dd class="text-right text-sm font-semibold text-white/85">
                                        {{ competition.category_name || 'General' }}
                                    </dd>
                                </div>

                                <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-sm text-white/45">Prizes listed</dt>
                                    <dd class="text-sm font-semibold text-white/85">
                                        {{ prizes.length }}
                                    </dd>
                                </div>

                                <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-sm text-white/45">Qualification</dt>
                                    <dd class="text-sm font-semibold text-white/85">
                                        {{ competition.qualification_enabled ? 'Required' : 'Not required' }}
                                    </dd>
                                </div>

                                <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-sm text-white/45">Instant win</dt>
                                    <dd class="text-sm font-semibold text-white/85">
                                        {{ competition.instant_win_enabled ? 'Available' : 'Not available' }}
                                    </dd>
                                </div>

                                <div class="flex items-start justify-between gap-4 py-3">
                                    <dt class="text-sm text-white/45">Eligible countries</dt>
                                    <dd class="max-w-[60%] text-right text-sm font-semibold text-white/85">
                                        <template v-if="countries.length">
                                            {{ countries.map(getCountryLabel).filter(Boolean).join(', ') }}
                                        </template>
                                        <template v-else>
                                            Not specified
                                        </template>
                                    </dd>
                                </div>

                                <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-sm text-white/45">Entry allocation</dt>
                                    <dd class="text-right text-sm font-semibold capitalize text-white/85">
                                        {{ (competition.entry_allocation_method || 'standard').replaceAll('_', ' ') }}
                                    </dd>
                                </div>

                                <!-- <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-sm text-white/45">Draw method</dt>
                                    <dd class="text-right text-sm font-semibold capitalize text-white/85">
                                        {{ (competition.draw_method || 'To be announced').replaceAll('_', ' ') }}
                                    </dd>
                                </div> -->

                                <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-sm text-white/45">Total entries</dt>
                                    <dd class="text-sm font-semibold text-white/85">
                                        {{ competition.total_entries ?? 'Not specified' }}
                                    </dd>
                                </div>

                                <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-sm text-white/45">Start date</dt>
                                    <dd class="text-right text-sm font-semibold text-white/85">
                                        {{ formatDate(competition.starts_at) }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </aside>
            </div>
        </main>
    </div>


    <!-- Qualification Questions Modal -->
    <Teleport to="body">
        <div v-if="showQualificationModal"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm"
            @click.self="showQualificationModal = false">
            <div class="w-full max-w-2xl overflow-hidden rounded-2xl border border-amber-500/25 bg-[#121212] shadow-2xl shadow-black/60"
                role="dialog" aria-modal="true" aria-labelledby="qualification-modal-title">
                <!-- Header -->
                <div class="border-b border-neutral-800 bg-gradient-to-r from-amber-950/50 to-transparent px-6 py-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-400">
                                Competition Entry
                            </p>
                            <h2 id="qualification-modal-title" class="mt-2 text-xl font-black text-white sm:text-2xl">
                                Qualification Questions
                            </h2>
                            <p class="mt-1 text-sm text-neutral-400">
                                Answer all questions to continue.
                            </p>
                        </div>

                        <button type="button" aria-label="Close qualification questions"
                            class="rounded-lg p-2 text-neutral-400 transition hover:bg-white/10 hover:text-white"
                            @click="showQualificationModal = false">
                            <span class="text-xl">&times;</span>
                        </button>
                    </div>
                </div>

                <!-- Questions -->
                <form class="max-h-[65vh] space-y-6 overflow-y-auto p-6" @submit.prevent="() => {
                    console.log('Qualification form submitted');
                    submitQualification();
                }">
                    <div v-for="(question, index) in modalQualificationQuestions" :key="question.id"
                        class="rounded-xl border border-neutral-800 bg-[#191919] p-4 sm:p-5">
                        <p class="mb-4 text-sm font-semibold leading-6 text-white">
                            <span class="mr-2 text-amber-400">
                                {{ index + 1 }}.
                            </span>
                            {{ question.question }}
                        </p>

                        <div class="space-y-3">
                            <label v-for="option in [
                                { key: 'a', text: question.option_a },
                                { key: 'b', text: question.option_b },
                                { key: 'c', text: question.option_c },
                                { key: 'd', text: question.option_d },
                            ].filter(option => option.text)" :key="option.key"
                                class="flex cursor-pointer items-start gap-3 rounded-lg border p-3 transition" :class="qualificationAnswers[question.id] === option.key
                                    ? 'border-amber-400/70 bg-amber-500/10'
                                    : 'border-neutral-800 hover:border-neutral-600'">
                                <input v-model="qualificationAnswers[question.id]" type="radio"
                                    :name="`qualification-${question.id}`" :value="option.key"
                                    class="mt-1 accent-amber-400" />

                                <span class="text-sm leading-5 text-neutral-200">
                                    {{ option.text }}
                                </span>
                            </label>
                        </div>
                    </div>

                    <p v-if="qualificationError"
                        class="rounded-lg border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-400" role="alert">
                        {{ qualificationError }}
                    </p>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" :disabled="submittingQualification"
                            class="rounded-xl border border-neutral-700 px-5 py-3 text-sm font-bold text-neutral-300 transition hover:bg-white/5 disabled:opacity-50"
                            @click="showQualificationModal = false">
                            Cancel
                        </button>

                        <button type="submit" :disabled="submittingQualification || !modalQualificationQuestions.length"
                            class="rounded-xl bg-gradient-to-r from-amber-400 via-[#eec76f] to-amber-500 px-6 py-3 text-sm font-black text-[#17120a] transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-50">
                            {{ submittingQualification ? 'Checking answers...' : 'Submit answers' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>



</template>
