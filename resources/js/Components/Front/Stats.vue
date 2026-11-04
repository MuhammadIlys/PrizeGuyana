<script setup>
defineProps({
  stats: {
    type: Array,
    default: () => []
  }
});
</script>

<template>
  <section class="w-full relative bg-[#0d0d0d] text-white py-16 px-4 font-sans overflow-hidden">
    <!-- Top Gradient Divider Line -->
    <div class="absolute top-0 left-0 right-0 h-[1px] bg-gradient-to-r from-transparent via-amber-500/30 to-transparent"></div>

    <div class="max-w-6xl mx-auto text-center">
      <h2 class="text-3xl sm:text-4xl font-black text-amber-500 tracking-tight mb-10">
        Our Stats
      </h2>

      <!-- Dynamic Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
        <div
          v-for="(stat, index) in stats"
          :key="index"
          class="relative bg-[#121212] border border-neutral-800/80 rounded-2xl py-8 px-6 flex flex-col items-center justify-center shadow-2xl transition hover:border-neutral-700 group overflow-hidden"
        >
          <!-- Soft Top-Center Glow Highlight -->
          <div class="absolute -top-12 left-1/2 -translate-x-1/2 w-32 h-16 bg-amber-500/20 blur-xl rounded-full pointer-events-none group-hover:bg-amber-500/30 transition-all"></div>

          <!-- Top Border Subtle Glow Accent -->
          <div class="absolute top-0 left-1/4 right-1/4 h-[1px] bg-gradient-to-r from-transparent via-amber-500/40 to-transparent"></div>

          <!-- Stat Value -->
          <span class="text-4xl sm:text-5xl font-extrabold text-amber-500 tracking-tight leading-none">
            {{ stat.value }}
          </span>

          <!-- Stat Label -->
          <span class="text-xs sm:text-sm text-neutral-300 font-medium tracking-wide mt-3">
            {{ stat.label }}
          </span>
        </div>
      </div>
    </div>

    <!-- Bottom Gradient Divider Line -->
    <div class="absolute bottom-0 left-0 right-0 h-[1px] bg-gradient-to-r from-transparent via-amber-500/30 to-transparent"></div>
  </section>
</template>