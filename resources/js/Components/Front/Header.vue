<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import logoUrl from '/resources/images/logo.png';

const mobileMenuOpen = ref(false);

const page = usePage();
const user = computed(() => page.props.auth?.user);

const logout = () => {
  router.post(route('logout'));
};
</script>

<template>
  <header
    class="border-b border-slate-800/80 bg-neutral-950/90 backdrop-blur sticky top-0 z-50 py-3 sm:py-5 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 sm:h-16 flex items-center justify-between">

      <!-- Brand / Logo -->
      <Link :href="route('home')" class="flex items-center gap-2">
        <span class="text-xl font-black tracking-tight text-orange-300">
          <img :src="logoUrl" alt="Prize Guyana Logo" class="h-12 sm:h-20 w-auto object-contain" />
        </span>
      </Link>

      <!-- Desktop Navigation Links -->
      <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
        <Link :href="route('home')" class="transition-colors hover:text-orange-400"
          :class="{ 'text-orange-300 font-semibold': $page.component === 'Front/Home' }">
          Home
        </Link>

        <Link :href="route('about')" class="transition-colors hover:text-orange-400"
          :class="{ 'text-orange-300 font-semibold': $page.component === 'Front/About' }">
          About
        </Link>

        <Link :href="route('contact')" class="transition-colors hover:text-orange-400"
          :class="{ 'text-orange-300 font-semibold': $page.component === 'Front/Contact' }">
          Contact
        </Link>
      </nav>

      <!-- Right Actions Container (Cart Icon + Portal Action Buttons) -->
      <div class="flex items-center gap-3">

        <!-- Desktop Action Buttons -->
        <div class="hidden md:flex items-center gap-3">
          <!-- Show Dashboard & Logout if User is Logged In -->
          <template v-if="user">
            <Link :href="user.role === 'admin' ? route('admin.dashboard') : route('user.dashboard')"
              class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-950 bg-slate-200 hover:bg-slate-300 rounded-sm transition-colors shadow-sm">
              Dashboard
            </Link>

            <button @click="logout"
              class="cursor-pointer px-4 py-2 text-xs font-bold uppercase tracking-wider text-white bg-red-600 hover:bg-red-700 rounded-sm transition-colors shadow-sm">
              Logout
            </button>
          </template>

          <!-- Show Login & Register if Guest -->
          <template v-else>
            <Link :href="route('login')"
              class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-950 bg-orange-300 hover:bg-orange-400 rounded-sm transition-colors shadow-sm">
              Login
            </Link>

            <Link :href="route('register')"
              class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-950 bg-orange-300 hover:bg-orange-400 rounded-sm transition-colors shadow-sm">
              Register
            </Link>
          </template>
        </div>

        <!-- Shopping Cart Icon Button (Visible on all screens) -->
        <Link :href="route('cart.index')"
          class="relative p-2 text-gray-300 hover:text-orange-300 hover:cursor-pointer transition-colors focus:outline-none inline-flex items-center"
          aria-label="View Shopping Cart">
          <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>

          <!-- Cart Count Badge Placeholder -->
          <span v-if="$page.props.cartCount > 0"
            class="absolute -top-0.5 -right-0.5 bg-amber-500 text-black font-extrabold text-[10px] min-w-4 h-4 px-1 rounded-full flex items-center justify-center">
            {{ $page.props.cartCount > 99 ? '99+' : $page.props.cartCount }}
          </span>
        </Link>

        <!-- Mobile Hamburger Button -->
        <button @click="mobileMenuOpen = !mobileMenuOpen" type="button"
          class="md:hidden p-2 rounded-md text-gray-300 hover:text-white hover:bg-slate-800 focus:outline-none"
          aria-label="Toggle menu">
          <svg v-if="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

    </div>

    <!-- Mobile Dropdown Menu -->
    <div v-show="mobileMenuOpen" class="md:hidden border-t border-slate-800 bg-neutral-950 px-4 pt-3 pb-6 space-y-4">
      <nav class="flex flex-col space-y-3 text-base font-medium">
        <Link :href="route('home')" @click="mobileMenuOpen = false" class="transition-colors hover:text-orange-400"
          :class="{ 'text-orange-300 font-semibold': $page.component === 'Front/Home' }">
          Home
        </Link>

        <Link :href="route('about')" @click="mobileMenuOpen = false" class="transition-colors hover:text-orange-400"
          :class="{ 'text-orange-300 font-semibold': $page.component === 'Front/About' }">
          About
        </Link>

        <Link :href="route('contact')" @click="mobileMenuOpen = false" class="transition-colors hover:text-orange-400"
          :class="{ 'text-orange-300 font-semibold': $page.component === 'Front/Contact' }">
          Contact
        </Link>
      </nav>

      <!-- Mobile Auth Action Buttons -->
      <div class="flex flex-col gap-2 pt-2 border-t border-slate-800">
        <!-- Logged-in State -->
        <template v-if="user">
          <Link :href="user.role === 'admin' ? route('admin.dashboard') : route('user.dashboard')"
            @click="mobileMenuOpen = false"
            class="w-full text-center px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-950 bg-slate-200 hover:bg-slate-300 rounded-sm transition-colors shadow-sm">
            Dashboard
          </Link>

          <button @click="logout(); mobileMenuOpen = false"
            class="cursor-pointer w-full text-center px-4 py-2 text-xs font-bold uppercase tracking-wider text-white bg-red-600 hover:bg-red-700 rounded-sm transition-colors shadow-sm">
            Logout
          </button>
        </template>

        <!-- Guest State -->
        <template v-else>
          <Link :href="route('login')" @click="mobileMenuOpen = false"
            class="w-full text-center px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-950 bg-orange-300 hover:bg-orange-400 rounded-sm transition-colors shadow-sm">
            Login
          </Link>

          <Link :href="route('register')" @click="mobileMenuOpen = false"
            class="w-full text-center px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-950 bg-orange-300 hover:bg-orange-400 rounded-sm transition-colors shadow-sm">
            Register
          </Link>
        </template>
      </div>
    </div>
  </header>
</template>