<script setup>
import { Link } from '@inertiajs/vue3';
import logoUrl from '/resources/images/logo.png'; // Adjust path if needed
</script>

<template>
  <footer
    class="relative w-full bg-[#0a0a0a] text-neutral-400 font-sans overflow-hidden pt-12 pb-8 border-t border-neutral-900"
    style="background: radial-gradient(circle at 100% 100%, rgba(140, 75, 10, 0.28) 0%, rgba(30, 20, 10, 0.45) 30%, rgba(10, 10, 10, 1) 75%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

      <!-- Top Row: Logo, Trustpilot Rating, Payment Methods -->
      <div class="flex flex-col md:flex-row items-center justify-between gap-6 pb-8">

        <!-- Logo -->
        <div class="flex items-center">
          <Link :href="route('home')">
            <img :src="logoUrl" alt="Logo" class="h-16 w-auto object-contain" />
          </Link>
        </div>

        <!-- Trustpilot Rating Banner -->
        <div class="flex flex-col items-center justify-center text-center">
          <p class="text-xs sm:text-sm text-neutral-300 font-medium">
            <span class="font-bold text-white">4.8</span> out of 5 based on <span class="font-bold text-white">717
              reviews</span>
          </p>
          <div class="flex items-center gap-1 mt-1.5">
            <span class="text-emerald-400 font-bold text-xs tracking-wider flex items-center gap-1 mr-1">
              <svg class="w-4 h-4 fill-emerald-400" viewBox="0 0 24 24">
                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
              </svg>
              Trustpilot
            </span>
            <!-- 5 Green Rating Stars -->
            <div class="flex items-center gap-0.5">
              <div v-for="i in 5" :key="i" class="bg-emerald-500 p-1 rounded-[2px] flex items-center justify-center">
                <svg class="w-3 h-3 fill-white" viewBox="0 0 24 24">
                  <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment Icons -->
        <div class="flex items-center gap-3 text-neutral-300">
          <span
            class="text-xs sm:text-sm font-semibold tracking-tight hover:text-white transition-colors cursor-pointer">Pay</span>
          <span
            class="text-xs sm:text-sm font-semibold tracking-tight hover:text-white transition-colors cursor-pointer">G
            Pay</span>
          <span
            class="text-xs sm:text-sm font-extrabold italic tracking-wider hover:text-white transition-colors cursor-pointer">VISA</span>
          <span
            class="text-xs sm:text-sm font-bold tracking-tight hover:text-white transition-colors cursor-pointer">Mastercard</span>
          <span
            class="text-xs sm:text-sm font-bold tracking-tight hover:text-white transition-colors cursor-pointer">PayPal</span>
        </div>

      </div>

      <!-- Gradient Top Border Divider Line -->
      <div class="w-full h-[1px] bg-gradient-to-r from-neutral-800 via-amber-500/40 to-neutral-800 my-2"></div>

      <!-- Links Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-8 py-10">

        <!-- Column 1: Information -->
        <div class="flex flex-col space-y-3">
          <h3 class="text-sm font-bold text-white tracking-wide">Information</h3>
          <ul class="space-y-2 text-xs sm:text-sm font-medium">
            <li>
              <Link :href="route('home')" class="hover:text-amber-400 transition-colors">FAQ</Link>
            </li>
            <li>
              <Link :href="route('about')" class="hover:text-amber-400 transition-colors">About</Link>
            </li>
            <li>
              <Link :href="route('home')" class="hover:text-amber-400 transition-colors">Winners</Link>
            </li>
            <li>
              <Link :href="route('contact')" class="hover:text-amber-400 transition-colors">Contact</Link>
            </li>
          </ul>
        </div>

        <!-- Column 2: Previous -->
        <div class="flex flex-col space-y-3">
          <h3 class="text-sm font-bold text-white tracking-wide">Previous</h3>
          <ul class="space-y-2 text-xs sm:text-sm font-medium">
            <li>
              <Link :href="route('home')" class="hover:text-amber-400 transition-colors">Competitions</Link>
            </li>
            <li>
              <Link :href="route('home')" class="hover:text-amber-400 transition-colors">Entry Lists</Link>
            </li>
          </ul>
        </div>

        <!-- Column 3: Support -->
        <div class="flex flex-col space-y-3">
          <h3 class="text-sm font-bold text-white tracking-wide">Support</h3>
          <ul class="space-y-2 text-xs sm:text-sm font-medium">
            <li>
              <Link :href="route('home')" class="hover:text-amber-400 transition-colors">My Account</Link>
            </li>
            <li>
              <Link :href="route('home')" class="hover:text-amber-400 transition-colors">My Tickets</Link>
            </li>
            <li>
              <Link :href="route('home')" class="hover:text-amber-400 transition-colors">Wallet</Link>
            </li>
            <li>
              <Link :href="route('home')" class="hover:text-amber-400 transition-colors">Cashback</Link>
            </li>
          </ul>
        </div>

        <!-- Column 4: Legal -->
        <div class="flex flex-col space-y-3">
          <h3 class="text-sm font-bold text-white tracking-wide">Legal</h3>
          <ul class="space-y-2 text-xs sm:text-sm font-medium">
            <li>
              <Link :href="route('privacypolicy')" class="hover:text-amber-400 transition-colors">Privacy Policy</Link>
            </li>
            <li>
              <Link :href="route('termsandconditions')" class="hover:text-amber-400 transition-colors">Terms and
                Conditions</Link>
            </li>
          </ul>
        </div>

      </div>

      <!-- Bottom Copyright & Social Icons Row -->
      <div
        class="pt-6 border-t border-neutral-900/80 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-neutral-500">

        <!-- Left Disclaimer -->
        <div class="text-center sm:text-left space-y-1">
          <p>© 2026 That PrizeGuyana. All rights reserved.</p>
          <p class="text-[11px] text-neutral-600">
            18+ only. Play responsibly:
          </p>
        </div>

        <!-- Right Social Icons -->
        <div class="flex items-center space-x-4 text-neutral-400">
          <!-- Facebook -->
          <a href="#" class="hover:text-amber-400 transition-colors" aria-label="Facebook">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
              <path
                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
            </svg>
          </a>
          <!-- Instagram -->
          <a href="#" class="hover:text-amber-400 transition-colors" aria-label="Instagram">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
              <path
                d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
            </svg>
          </a>
          <!-- Twitter / X -->
          <a href="#" class="hover:text-amber-400 transition-colors" aria-label="Twitter">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
              <path
                d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
            </svg>
          </a>
          <!-- TikTok -->
          <a href="#" class="hover:text-amber-400 transition-colors" aria-label="TikTok">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
              <path
                d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.98-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.28-2.36.54-4.74 2.17-6.43 1.58-1.67 3.82-2.58 6.13-2.48.02 1.38-.01 2.76 0 4.14-.85-.12-1.74.03-2.49.46-.92.51-1.57 1.44-1.72 2.48-.2 1.18.2 2.39 1.04 3.21.82.83 2.01 1.21 3.16 1.02 1.08-.16 2.03-.83 2.53-1.8.35-.67.48-1.44.47-2.19-.01-4.77-.01-9.54-.01-14.31z" />
            </svg>
          </a>
        </div>

      </div>

    </div>
  </footer>
</template>