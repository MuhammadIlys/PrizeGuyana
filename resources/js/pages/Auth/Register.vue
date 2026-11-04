<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import logoUrl from '/resources/images/logo.png';
import FrontLayout from '../../Layouts/FrontLayout.vue';
import CountrySelect from '@/Components/Global/CountrySelect.vue';

defineOptions({
  layout: FrontLayout,
});

const form = useForm({
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  password_confirmation: '',
  country: 'Guyana',
  date_of_birth: '',
  phone_number: '',
  terms: false,
  privacy: false,
});

const submit = () => {
  form.post(route('user.register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>
 
<template>
  <Head title="Create Account" />

  <div
    class="min-h-screen w-full bg-[#0a0a0a] text-white flex flex-col justify-center items-center px-4 py-12 relative overflow-hidden font-sans"
    style="background: radial-gradient(circle at 0% 0%, rgba(130, 65, 10, 0.35) 0%, rgba(20, 15, 10, 0.7) 40%, rgba(10, 10, 10, 1) 80%);">
    
    <div class="absolute top-0 left-0 right-0 h-[1px] bg-gradient-to-r from-transparent via-amber-500/30 to-transparent"></div>

    <div class="w-full max-w-xl z-10">

      <!-- Logo Header -->
      <div class="text-center mb-8">
        <Link :href="route('home')" class="inline-block">
          <img :src="logoUrl" alt="Logo" class="h-16 mx-auto object-contain drop-shadow-md" />
        </Link>
        <h1 class="mt-4 text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
          Create an Account
        </h1>
        <p class="mt-1.5 text-xs sm:text-sm text-neutral-400">
          Join today to enter competitions &amp; track your entries
        </p>
      </div>

      <!-- Main Register Form Box -->
      <div class="bg-[#121212]/90 backdrop-blur-md border border-neutral-800/90 rounded-2xl p-6 sm:p-8 shadow-2xl relative">
        <div class="absolute top-0 left-1/4 right-1/4 h-[1px] bg-gradient-to-r from-transparent via-amber-500/40 to-transparent"></div>

        <form @submit.prevent="submit" class="space-y-4">

          <!-- First & Last Name Row -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- First Name -->
            <div>
              <label for="first_name" class="block text-xs font-bold text-neutral-300 uppercase tracking-wider mb-1.5">
                First Name <span class="text-amber-500">*</span>
              </label>
              <div class="relative rounded-xl bg-[#0a0a0a] border border-neutral-800 focus-within:border-amber-500 transition-colors shadow-inner">
                <input id="first_name" v-model="form.first_name" type="text" required autofocus
                  autocomplete="given-name" placeholder="John"
                  class="w-full bg-transparent px-4 py-3 text-sm text-white placeholder-neutral-600 focus:outline-none border-none ring-0 focus:ring-0 rounded-xl" />
              </div>
              <p v-if="form.errors.first_name" class="mt-1 text-xs text-red-500 font-medium">
                {{ form.errors.first_name }}
              </p>
            </div>

            <!-- Last Name -->
            <div>
              <label for="last_name" class="block text-xs font-bold text-neutral-300 uppercase tracking-wider mb-1.5">
                Last Name <span class="text-amber-500">*</span>
              </label>
              <div class="relative rounded-xl bg-[#0a0a0a] border border-neutral-800 focus-within:border-amber-500 transition-colors shadow-inner">
                <input id="last_name" v-model="form.last_name" type="text" required autocomplete="family-name"
                  placeholder="Doe"
                  class="w-full bg-transparent px-4 py-3 text-sm text-white placeholder-neutral-600 focus:outline-none border-none ring-0 focus:ring-0 rounded-xl" />
              </div>
              <p v-if="form.errors.last_name" class="mt-1 text-xs text-red-500 font-medium">
                {{ form.errors.last_name }}
              </p>
            </div>
          </div>

          <!-- Email Input -->
          <div>
            <label for="email" class="block text-xs font-bold text-neutral-300 uppercase tracking-wider mb-1.5">
              Email Address <span class="text-amber-500">*</span>
            </label>
            <div class="relative rounded-xl bg-[#0a0a0a] border border-neutral-800 focus-within:border-amber-500 transition-colors shadow-inner">
              <input id="email" v-model="form.email" type="email" required autocomplete="username"
                placeholder="you@example.com"
                class="w-full bg-transparent px-4 py-3 text-sm text-white placeholder-neutral-600 focus:outline-none border-none ring-0 focus:ring-0 rounded-xl" />
            </div>
            <p v-if="form.errors.email" class="mt-1 text-xs text-red-500 font-medium">
              {{ form.errors.email }}
            </p>
          </div>

          <!-- Password & Confirm Password Row -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Password Input -->
            <div>
              <label for="password" class="block text-xs font-bold text-neutral-300 uppercase tracking-wider mb-1.5">
                Password <span class="text-amber-500">*</span>
              </label>
              <div class="relative rounded-xl bg-[#0a0a0a] border border-neutral-800 focus-within:border-amber-500 transition-colors shadow-inner">
                <input id="password" v-model="form.password" type="password" required autocomplete="new-password"
                  placeholder="••••••••"
                  class="w-full bg-transparent px-4 py-3 text-sm text-white placeholder-neutral-600 focus:outline-none border-none ring-0 focus:ring-0 rounded-xl" />
              </div>
              <p v-if="form.errors.password" class="mt-1 text-xs text-red-500 font-medium">
                {{ form.errors.password }}
              </p>
            </div>

            <!-- Confirm Password Input -->
            <div>
              <label for="password_confirmation" class="block text-xs font-bold text-neutral-300 uppercase tracking-wider mb-1.5">
                Confirm Password <span class="text-amber-500">*</span>
              </label>
              <div class="relative rounded-xl bg-[#0a0a0a] border border-neutral-800 focus-within:border-amber-500 transition-colors shadow-inner">
                <input id="password_confirmation" v-model="form.password_confirmation" type="password" required
                  autocomplete="new-password" placeholder="••••••••"
                  class="w-full bg-transparent px-4 py-3 text-sm text-white placeholder-neutral-600 focus:outline-none border-none ring-0 focus:ring-0 rounded-xl" />
              </div>
              <p v-if="form.errors.password_confirmation" class="mt-1 text-xs text-red-500 font-medium">
                {{ form.errors.password_confirmation }}
              </p>
            </div>
          </div>

          <!-- Country & Date of Birth Row -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Country of Residence -->
            <div>
              <label for="country" class="block text-xs font-bold text-neutral-300 uppercase tracking-wider mb-1.5">
                Country of Residence <span class="text-amber-500">*</span>
              </label>
              <CountrySelect v-model="form.country" :error="form.errors.country" />
              <p v-if="form.errors.country" class="mt-1 text-xs text-red-500 font-medium">
                {{ form.errors.country }}
              </p>
            </div>

            <!-- Date of Birth -->
            <div>
              <label for="date_of_birth" class="block text-xs font-bold text-neutral-300 uppercase tracking-wider mb-1.5">
                Date of Birth <span class="text-amber-500">*</span>
              </label>
              <div class="relative rounded-xl bg-[#0a0a0a] border border-neutral-800 focus-within:border-amber-500 transition-colors shadow-inner">
                <input id="date_of_birth" v-model="form.date_of_birth" type="date" required
                  class="w-full bg-transparent px-4 py-3 text-sm text-white placeholder-neutral-600 focus:outline-none border-none ring-0 focus:ring-0 rounded-xl [color-scheme:dark]" />
              </div>
              <p v-if="form.errors.date_of_birth" class="mt-1 text-xs text-red-500 font-medium">
                {{ form.errors.date_of_birth }}
              </p>
            </div>
          </div>

          <!-- Phone Number (Optional) -->
          <div>
            <label for="phone_number" class="block text-xs font-bold text-neutral-300 uppercase tracking-wider mb-1.5">
              Phone Number <span class="text-neutral-500 font-normal lowercase">(optional)</span>
            </label>
            <div class="relative rounded-xl bg-[#0a0a0a] border border-neutral-800 focus-within:border-amber-500 transition-colors shadow-inner">
              <input id="phone_number" v-model="form.phone_number" type="tel" autocomplete="tel"
                placeholder="+592 600 0000"
                class="w-full bg-transparent px-4 py-3 text-sm text-white placeholder-neutral-600 focus:outline-none border-none ring-0 focus:ring-0 rounded-xl" />
            </div>
            <p v-if="form.errors.phone_number" class="mt-1 text-xs text-red-500 font-medium">
              {{ form.errors.phone_number }}
            </p>
          </div>

          <!-- Terms & Privacy Checkboxes -->
          <div class="space-y-3 pt-2">
            <!-- Agree to Terms -->
            <div class="flex items-start gap-3">
              <div class="flex items-center h-5">
                <input id="terms" v-model="form.terms" type="checkbox" required
                  class="w-4 h-4 rounded border-neutral-800 bg-[#0a0a0a] text-amber-500 focus:ring-amber-500/20 focus:ring-offset-0 cursor-pointer" />
              </div>
              <label for="terms" class="text-xs text-neutral-300 cursor-pointer leading-tight">
                I agree to the
                <Link :href="route('termsandconditions')" target="_blank"
                  class="text-amber-400 hover:underline font-semibold">Terms &amp; Conditions</Link>
                <span class="text-amber-500">*</span>
              </label>
            </div>
            <p v-if="form.errors.terms" class="text-xs text-red-500 font-medium pl-7">
              {{ form.errors.terms }}
            </p>

            <!-- Agree to Privacy Policy -->
            <div class="flex items-start gap-3">
              <div class="flex items-center h-5">
                <input id="privacy" v-model="form.privacy" type="checkbox" required
                  class="w-4 h-4 rounded border-neutral-800 bg-[#0a0a0a] text-amber-500 focus:ring-amber-500/20 focus:ring-offset-0 cursor-pointer" />
              </div>
              <label for="privacy" class="text-xs text-neutral-300 cursor-pointer leading-tight">
                I agree to the
                <Link :href="route('privacypolicy')" target="_blank"
                  class="text-amber-400 hover:underline font-semibold">Privacy Policy</Link>
                <span class="text-amber-500">*</span>
              </label>
            </div>
            <p v-if="form.errors.privacy" class="text-xs text-red-500 font-medium pl-7">
              {{ form.errors.privacy }}
            </p>
          </div>

          <!-- Submit Button -->
          <button type="submit" :disabled="form.processing"
            class="w-full py-3.5 px-4 bg-gradient-to-r from-amber-400 via-yellow-500 to-amber-500 hover:brightness-110 active:scale-[0.99] text-black font-extrabold text-sm rounded-xl transition-all shadow-lg border border-yellow-300/30 disabled:opacity-50 !mt-6 cursor-pointer">
            {{ form.processing ? 'Creating Account...' : 'Register Now' }}
          </button>

        </form>

        <!-- Divider Line -->
        <div class="relative my-6 text-center">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-neutral-800"></div>
          </div>
          <span class="relative bg-[#121212] px-3 text-xs text-neutral-500 uppercase font-semibold">
            Or
          </span>
        </div>

        <!-- Login Redirect Link -->
        <div class="text-center">
          <p class="text-xs text-neutral-400 font-medium">
            Already have an account?
            <Link :href="route('login')" class="text-amber-500 hover:underline font-bold ml-1">
              Log in instead
            </Link>
          </p>
        </div>

      </div>

      <!-- Footer Age Disclaimer -->
      <div class="mt-6 text-center text-xs text-neutral-500">
        <p>Must be 18 years or older to register and enter competitions.</p>
      </div>

    </div>
  </div>
</template>