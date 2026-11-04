<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import FrontLayout from '@/Layouts/FrontLayout.vue';
import HomeSlider from '../../Components/Front/HomeSlider.vue';
import Featured from '../../Components/Front/Featured.vue';
import EndingToday from '../../Components/Front/EndingToday.vue';
import Tomorrow from '../../Components/Front/Tomorrow.vue';
import FutureSelection from '../../Components/Front/FutureSelection.vue';
import LatestUpdates from '../../Components/Front/LatestUpdates.vue';
import NewsletterSection from '../../Components/Front/NewsletterSection.vue';
import Stats from '../../Components/Front/Stats.vue';
import FAQs from '../../Components/Front/FAQs.vue';

defineOptions({ layout: FrontLayout });
const props = defineProps({
  title: String,
  slides: {
    type: Array,
    default: () => []
  },
  featuredCompetitions: {
    type: Array,
    default: () => []
  },
  endingToday: {
    type: Array,
    default: () => []
  },
  tomorrow:{
    type : Array,
    default: () => []
  },
  futureSelection : {
    type : Array,
    default : () => []
  },
  updates : {
    type : Array,
    default : () => []
  },
   stats : {
    type : Array,
    default : () => []
  },
  faqs: {
        type: Array,
        default: () => [],
    },
});


</script>

<template>
  <HomeSlider :slides="slides" />
  <Featured :competitions="featuredCompetitions" />
  <EndingToday :endingToday="endingToday" />
  <Tomorrow :tomorrow="tomorrow" />
  <FutureSelection :futureSelection="futureSelection" />
  <LatestUpdates :updates="updates" />
  <FAQs :faqs="faqs" />
  <NewsletterSection />
  <Stats :stats="stats" />

</template>

<style scoped></style>