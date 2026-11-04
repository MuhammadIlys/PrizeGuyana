<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import FrontLayout from '../../Layouts/FrontLayout.vue';
import { useToast } from '../../Composables/useToast';
import { formatDate } from '../../helpers/date.js';

defineOptions({
    layout: FrontLayout,
});

const toast = useToast();

const props = defineProps({
    items: {
        type: Array,
        default: () => [],
    },
});

// Keep a local reactive copy so totals update immediately in the UI.
const items = ref(props.items.map(item => ({ ...item })));
const processingItems = ref([]);
const isClearingCart = ref(false);

const subtotal = computed(() =>
    items.value.reduce(
        (total, item) => total + Number(item.ticket_price || 0) * Number(item.quantity || 0),
        0,
    ),
);

const totalTickets = computed(() =>
    items.value.reduce((total, item) => total + Number(item.quantity || 0), 0),
);

const isProcessing = (id) => processingItems.value.includes(Number(id));

const updateQuantity = (item, nextQuantity) => {
    const itemId = Number(item.id);
    const previousQuantity = Number(item.quantity);
    const quantity = Number(nextQuantity);
    const maxTickets = item.max_tickets == null
        ? null
        : Number(item.max_tickets);

    if (
        isProcessing(itemId) ||
        quantity < 1 ||
        quantity === previousQuantity
    ) {
        return;
    }

    if (maxTickets !== null && quantity > maxTickets) {
        toast.warning(
            `Maximum limit reached! You can only buy up to ${maxTickets} tickets for ${item.title}.`
        );
        return;
    }

    item.quantity = quantity;
    processingItems.value.push(itemId);

    router.patch(route('cart.update', itemId), { quantity }, {
        preserveScroll: true,
        onError: (errors) => {
            const currentItem = items.value.find(
                cartItem => Number(cartItem.id) === itemId
            );

            if (currentItem) {
                currentItem.quantity = previousQuantity;
            }

            toast.warning(
                errors.quantity || errors.message ||
                'Could not update the quantity. Please try again.'
            );
        },
        onFinish: () => {
            processingItems.value = processingItems.value.filter(
                id => id !== itemId
            );
        },
    });
};

const incrementQuantity = (item) => {
    const maxTickets = item.max_tickets == null
        ? null
        : Number(item.max_tickets);

    if (
        maxTickets !== null &&
        Number(item.quantity) >= maxTickets
    ) {
        toast.warning(
            `Maximum limit reached! You can only buy up to ${maxTickets} tickets for ${item.title}.`
        );
        return;
    }

    updateQuantity(item, Number(item.quantity) + 1);
};

const decrementQuantity = (item) => {
    if (Number(item.quantity) > 1) {
        updateQuantity(item, Number(item.quantity) - 1);
    }
};

const removeItem = (id) => {
    const itemId = Number(id);
    if (isProcessing(itemId)) return;

    const itemIndex = items.value.findIndex(item => Number(item.id) === itemId);
    if (itemIndex === -1) return;

    const [removedItem] = items.value.splice(itemIndex, 1);
    processingItems.value.push(itemId);

    router.delete(route('cart.remove', itemId), {
        preserveScroll: true,
        onError: () => {
            // Restore the item if the server rejects the removal.
            items.value.splice(Math.min(itemIndex, items.value.length), 0, removedItem);
            toast.warning('Could not remove this competition. Please try again.');
        },
        onFinish: () => {
            processingItems.value = processingItems.value.filter(processingId => processingId !== itemId);
        },
    });
};

// Clear the cart using the existing remove route, one request at a time.
// Sequential requests avoid competing session writes and do not require a new route.
const clearCart = () => {
    if (isClearingCart.value || items.value.length === 0) return;

    if (!window.confirm('Are you sure you want to remove all items from your cart?')) {
        return;
    }

    isClearingCart.value = true;
    const idsToRemove = items.value.map(item => Number(item.id));

    const removeNext = (index) => {
        if (index >= idsToRemove.length) {
            items.value = [];
            isClearingCart.value = false;
            return;
        }

        const itemId = idsToRemove[index];
        const itemIndex = items.value.findIndex(item => Number(item.id) === itemId);
        const itemToRestore = itemIndex >= 0 ? items.value[itemIndex] : null;

        let requestSucceeded = false;

        router.delete(route('cart.remove', itemId), {
            preserveScroll: true,
            onSuccess: () => {
                requestSucceeded = true;
                items.value = items.value.filter(item => Number(item.id) !== itemId);
            },
            onError: () => {
                if (itemToRestore && !items.value.some(item => Number(item.id) === itemId)) {
                    items.value.splice(Math.min(itemIndex, items.value.length), 0, itemToRestore);
                }
                isClearingCart.value = false;
                toast.warning('The cart could not be completely cleared. Please try again.');
            },
            onFinish: () => {
                if (requestSucceeded) removeNext(index + 1);
            },
        });
    };

    removeNext(0);
};
</script>

<template>

    <Head title="Your Cart" />

    <div class="min-h-screen bg-[#0c0c0c] text-white py-12 sm:py-16 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        <!-- Ambient Top-Right Radial Background Glow -->
        <div class="absolute top-0 right-0 w-[600px] h-[600px] pointer-events-none opacity-40"
            style="background: radial-gradient(circle at 100% 0%, rgba(245, 158, 11, 0.25) 0%, rgba(28, 20, 15, 0.6) 45%, rgba(12, 12, 12, 0) 100%);">
        </div>

        <div class="max-w-7xl mx-auto relative z-10">

            <!-- Page Header -->
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-neutral-800/80 pb-6 mb-8 gap-4">
                <div>
                    <span
                        class="text-xs font-extrabold uppercase tracking-widest text-amber-500 bg-amber-500/10 px-3 py-1 rounded-full border border-amber-500/20">
                        Ticket Overview
                    </span>
                    <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight mt-3">
                        Shopping Cart
                    </h1>
                </div>

                <Link :href="route('home')"
                    class="inline-flex items-center gap-2 text-sm text-neutral-400 hover:text-amber-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Continue Browsing
                </Link>
            </div>

            <!-- Cart Content Grid -->
            <div v-if="items.length > 0" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- Items List (8 Cols) -->
                <div class="lg:col-span-8 space-y-4">
                    <div v-for="item in items" :key="item.id"
                        class="bg-[#121212] border border-neutral-800/80 hover:border-neutral-700/80 rounded-2xl p-4 sm:p-6 transition-all duration-300 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-6">
                        <!-- Thumbnail & Details -->
                        <div class="flex items-center gap-4 sm:gap-6 w-full sm:w-auto">
                            <div
                                class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl bg-neutral-900 border border-neutral-800 flex-shrink-0 overflow-hidden relative">
                                <img :src="item.image" :alt="item.title" class="w-full h-full object-cover" />
                            </div>

                            <div class="flex-1 min-w-0">
                                <span
                                    class="text-[10px] font-bold uppercase tracking-wider text-amber-500/90 block mb-1">
                                    {{ item.category }}
                                </span>
                                <h3 class="text-base sm:text-lg font-bold text-white truncate">
                                    {{ item.title }}
                                </h3>
                                <div class="flex items-center gap-3 text-xs text-neutral-400 mt-1">
                                    <span>Price: <strong class="text-amber-400">${{ item.ticket_price.toFixed(2)
                                    }}</strong></span>
                                    <span>•</span>
                                    <span>Draw: {{ formatDate(item.draw_at) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quantity Stepper & Price Summary -->
                        <div
                            class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto border-t sm:border-t-0 border-neutral-800/60 pt-4 sm:pt-0">

                            <!-- Quantity Controls -->
                            <div class="flex items-center bg-[#0a0a0a] border border-neutral-800 rounded-lg p-1">
                                <button @click="decrementQuantity(item)"
                                    :disabled="isProcessing(item.id) || isClearingCart || item.quantity <= 1"
                                    type="button"
                                    class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-white hover:bg-neutral-800 rounded-md transition-colors">
                                    -
                                </button>
                                <span class="w-10 text-center text-sm font-bold text-white">
                                    {{ item.quantity }}
                                </span>
                                <button @click="incrementQuantity(item)"
                                    :disabled="isProcessing(item.id) || isClearingCart" type="button"
                                    class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-white hover:bg-neutral-800 rounded-md transition-colors">
                                    +
                                </button>
                            </div>

                            <!-- Total Price -->
                            <div class="text-right min-w-[90px]">
                                <span class="text-xs text-neutral-500 block">Total</span>
                                <span class="text-lg font-black text-amber-400">
                                    ${{ (item.ticket_price * item.quantity).toFixed(2) }}
                                </span>
                            </div>

                            <!-- Remove Button -->
                            <button @click="removeItem(item.id)" :disabled="isProcessing(item.id) || isClearingCart"
                                type="button"
                                class="p-2 text-neutral-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors"
                                title="Remove Item">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Bottom Clear Toolbar -->
                    <div class="flex justify-between items-center pt-2">
                        <button @click="clearCart" :disabled="isClearingCart" type="button"
                            class="text-xs font-semibold text-neutral-500 hover:text-red-400 transition-colors flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Clear Cart
                        </button>
                    </div>
                </div>

                <!-- Order Summary Sidebar (4 Cols) -->
                <div
                    class="lg:col-span-4 bg-[#121212] border border-neutral-800/80 rounded-2xl p-6 sm:p-8 space-y-6 shadow-2xl relative">
                    <div
                        class="absolute top-0 left-1/4 right-1/4 h-[1px] bg-gradient-to-r from-transparent via-amber-500/30 to-transparent">
                    </div>

                    <h2 class="text-xl font-black text-white border-b border-neutral-800/80 pb-4">
                        Order Summary
                    </h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-neutral-400">
                            <span>Total Tickets</span>
                            <span class="font-bold text-white">{{ totalTickets }}</span>
                        </div>
                        <div class="flex justify-between text-neutral-400">
                            <span>Subtotal</span>
                            <span class="font-bold text-white">${{ subtotal.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between text-neutral-400">
                            <span>Processing Fee</span>
                            <span class="text-emerald-400 font-semibold">FREE</span>
                        </div>
                    </div>

                    <div class="border-t border-neutral-800/80 pt-4 flex justify-between items-center">
                        <div>
                            <span class="text-xs text-neutral-400 uppercase tracking-wider block font-bold">Estimated
                                Total</span>
                            <span class="text-2xl font-black text-amber-400">${{ subtotal.toFixed(2) }}</span>
                        </div>
                    </div>

                    <!-- Checkout CTA Button -->

                    <Link :href="route('checkout.index')"
                        class="block w-full text-center cursor-pointer py-3.5 px-6 text-sm font-extrabold uppercase tracking-wider text-black bg-gradient-to-r from-amber-400 via-yellow-500 to-amber-500 hover:brightness-110 rounded-xl transition-all shadow-lg active:scale-[0.99]">
                        Proceed to Checkout
                    </Link>


                    <!-- Trust Badges -->
                    <div class="pt-2 border-t border-neutral-800/60 space-y-2 text-xs text-neutral-400">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span>100% Encrypted & Secure Checkout</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>Instant Ticket Numbers Allocated</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Empty Cart State -->
            <div v-else
                class="text-center py-20 bg-[#121212] border border-neutral-800/80 rounded-2xl max-w-2xl mx-auto space-y-6">
                <div
                    class="w-16 h-16 mx-auto rounded-full bg-neutral-900 border border-neutral-800 flex items-center justify-center text-neutral-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="space-y-2">
                    <h3 class="text-2xl font-bold text-white">Your cart is empty</h3>
                    <p class="text-neutral-400 text-sm max-w-sm mx-auto">
                        You haven't added any competition tickets yet.
                    </p>
                </div>
                <div>
                    <Link :href="route('home')"
                        class="inline-flex items-center gap-2 px-6 py-3 text-xs font-extrabold uppercase tracking-wider text-black bg-amber-400 hover:bg-amber-300 rounded-xl transition-all">
                        Explore Active Draws
                    </Link>
                </div>
            </div>

        </div>
    </div>
</template>