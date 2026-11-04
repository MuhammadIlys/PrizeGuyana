<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
  email: '',
});

const submit = () => {
  form.post(route('newsletter.subscribe'), {
    preserveScroll: true,
    onSuccess: () => form.reset('email'),
  });
};
</script>

<template>
  <section class="w-full max-w-7xl mx-auto px-0 py-12">
    <!-- Main Card Container with Top-Left Radial Gradient -->
    <div
      class="relative w-full rounded-3xl border border-neutral-800/80 p-8 sm:p-12 lg:p-16 overflow-hidden flex flex-col lg:flex-row items-center justify-between gap-8 shadow-2xl"
      style="background: radial-gradient(circle at 0% 0%, rgba(130, 65, 10, 0.45) 0%, rgba(28, 20, 15, 0.8) 35%, rgba(12, 12, 12, 1) 75%);">

      <!-- Left Column: Heading & Description -->
      <div class="max-w-xl text-left z-10">
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-amber-500 tracking-tight leading-tight">
          Do you like discount codes?
        </h2>
        <p class="mt-4 text-neutral-300 text-sm sm:text-base lg:text-lg font-normal leading-relaxed">
          The latest codes, competitions &amp; secret competitions sent to your inbox daily.
        </p>
      </div>

      <!-- Right Column: Form Container -->
      <div class="w-full lg:w-auto min-w-full sm:min-w-[450px] z-10">
        <form @submit.prevent="submit" class="flex flex-col gap-3">

          <!-- Input Pill Container (Responsive Grid / Flex Stack) -->
          <div
            class="relative flex flex-col sm:flex-row items-stretch sm:items-center bg-[#0d0d0d]/90 backdrop-blur-sm border border-neutral-800 rounded-2xl p-1.5 sm:p-1.5 gap-2 sm:gap-0 focus-within:border-amber-500/50 transition-colors shadow-inner">
            <input v-model="form.email" type="email" id="email" required placeholder="Enter your email address"
              class="w-full bg-transparent px-4 py-3 text-sm text-white placeholder-neutral-500 focus:outline-none border-none ring-0 focus:ring-0 rounded-xl sm:rounded-none" />
            <button type="submit" :disabled="form.processing"
              class="w-full sm:w-auto flex-shrink-0 px-6 py-3 bg-neutral-800 hover:bg-neutral-700 active:scale-[0.98] text-white font-semibold text-sm rounded-xl border border-neutral-700/80 transition-all shadow-md disabled:opacity-50 text-center">
              {{ form.processing ? 'Saving...' : 'Notify Me' }}
            </button>
          </div>

          <!-- Privacy Notice -->
          <p class="text-xs text-neutral-400 font-medium px-2 text-center sm:text-left">
            We care about the protection of your data. Read our
            <Link :href="route('home')" class="text-amber-500 hover:underline font-semibold">
              Privacy Policy.
            </Link>
          </p>

        </form>
      </div>

    </div>
  </section>
</template>