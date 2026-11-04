<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  competitions: {
    type: Array,
    default: () => []
  }
});
</script>

<template>
  <section class="w-full max-w-7xl mx-auto px-4 py-14 font-sans">

    <!-- Top Skewed "FEATURED" Header Tag -->
    <div class="mb-4 inline-block">
      <div
        class="bg-amber-500 text-black font-extrabold text-sm sm:text-base tracking-wider px-6 py-1.5 transform -skew-x-12 rounded-sm shadow-md uppercase">
        <span class="inline-block transform skew-x-12">FEATURED</span>
      </div>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5"
      style="background: radial-gradient(circle at 50% 50%, rgba(130, 65, 10, 0.45) 0%, rgba(28, 20, 15, 0.8) 50%, rgba(12, 12, 12, 1) 100%);">
      <div v-for="item in competitions" :key="item.id"
        class="bg-[#121212] border border-neutral-800/80 rounded-xl p-3 flex flex-col justify-between shadow-2xl relative">
        <!-- Card Container -->
        <div class="relative w-full">

          <!-- Top Centered Date Badge -->
          <div class="absolute -top-3 left-1/2 -translate-x-1/2 z-20">
            <span
              class="bg-[#2a2a2a] text-white text-[11px] font-bold px-5 py-2 rounded-md border border-neutral-600 shadow uppercase">
              {{ item.date_badge }}
            </span>
          </div>

          <!-- Main Image Canvas (Contains big textured WIN / CASH graphics) -->
          <div
            class="relative w-full aspect-square rounded-lg overflow-hidden border border-neutral-800 bg-black flex items-center justify-center">
            <img :src="item.image" :alt="`Competition ${item.id}`" class="w-full h-full object-cover object-center" />
          </div>

          <!-- Progress Bar & Percentage Pill -->
          <div class="mt-3 px-1">
            <div
              class="relative w-full h-3.5 bg-[#1a1a1a] rounded-full border border-neutral-800 p-0.5 flex items-center">

              <!-- Progress Fill -->
              <div class="h-full bg-gradient-to-r from-amber-500 to-yellow-400 rounded-full transition-all duration-300"
                :style="{ width: `${item.percentage}%` }"></div>

              <!-- Percentage Pill Inside/Over Progress -->
              <div
                class="absolute left-1 flex items-center justify-center bg-[#222] border border-neutral-700 text-white font-extrabold text-[10px] px-2 py-0.5 rounded-full shadow">
                {{ item.percentage }}%
              </div>
            </div>
          </div>

        </div>

        <!-- Yellow CTA Button -->
        <div class="mt-4">
          <Link :href="route('competition.detail', item.id)"
            class="block w-full text-center py-2.5 bg-gradient-to-r from-amber-400 via-yellow-500 to-amber-500 hover:brightness-110 active:scale-[0.99] text-black font-extrabold text-xs sm:text-sm rounded-lg transition shadow-md border border-yellow-300/30">
            {{ item.title }} <br> {{ item.price }}
          </Link>
        </div>

      </div>
    </div>

  </section>
</template>