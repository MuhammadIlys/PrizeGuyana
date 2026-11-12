<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  slides: {
    type: Array,
    required: true,
    default: () => []
  }
});

const sliderRef = ref(null);
const currentSlide = ref(0);
const activeIndex = ref(0);
const isTransitioning = ref(true);

let timer = null;

// Triple the slides for infinite looping
const infiniteSlides = computed(() => {
  return [
    ...props.slides,
    ...props.slides,
    ...props.slides
  ];
});

// Get actual slide position
const updatePosition = (animate = true) => {
  const container = sliderRef.value;

  if (!container) return;

  const slide = container.children[activeIndex.value];

  if (!slide) return;

  isTransitioning.value = animate;

  const containerWidth = container.parentElement.clientWidth;
  const slideWidth = slide.offsetWidth;

  // Center the active slide
  const slideLeft = slide.offsetLeft;
  const translateX =
    slideLeft - (containerWidth - slideWidth) / 2;

  container.style.transform = `translateX(-${translateX}px)`;
};

// Move one slide
const goToSlide = (index, animate = true) => {
  activeIndex.value = index;

  if (props.slides.length) {
    currentSlide.value =
      ((index % props.slides.length) +
        props.slides.length) %
      props.slides.length;
  }

  nextTick(() => {
    updatePosition(animate);
  });
};

// Next slide — EXACTLY ONE
const nextSlide = () => {
  if (props.slides.length <= 1) return;

  goToSlide(activeIndex.value + 1);
};

// Previous slide — EXACTLY ONE
const prevSlide = () => {
  if (props.slides.length <= 1) return;

  goToSlide(activeIndex.value - 1);
};

// After animation finishes
const handleTransitionEnd = () => {
  const length = props.slides.length;

  if (!length) return;

  // Reached third copy
  if (activeIndex.value >= length * 2) {
    activeIndex.value -= length;

    currentSlide.value = activeIndex.value % length;

    nextTick(() => {
      updatePosition(false);

      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          isTransitioning.value = true;
        });
      });
    });
  }

  // Reached first copy
  else if (activeIndex.value < length) {
    activeIndex.value += length;

    currentSlide.value = activeIndex.value % length;

    nextTick(() => {
      updatePosition(false);

      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          isTransitioning.value = true;
        });
      });
    });
  }
};

// Dot click
const goToOriginalSlide = (index) => {
  goToSlide(props.slides.length + index);
};

// Auto scroll
const startAutoScroll = () => {
  stopAutoScroll();

  if (props.slides.length > 1) {
    timer = setInterval(() => {
      nextSlide();
    }, 3000);
  }
};

const stopAutoScroll = () => {
  if (timer) {
    clearInterval(timer);
    timer = null;
  }
};

const handleResize = () => {
  updatePosition(false);
};

onMounted(() => {
  // Start from middle copy
  activeIndex.value = props.slides.length;

  nextTick(() => {
    updatePosition(false);

    window.addEventListener('resize', handleResize);

    startAutoScroll();
  });
});

onUnmounted(() => {
  stopAutoScroll();
  window.removeEventListener('resize', handleResize);
});
</script>

<template>
  <section v-if="props.slides.length" class="space-y-6"
    style="background: radial-gradient(farthest-corner at 100% 0%, rgba(130, 65, 10, 0.45) 0%, rgba(28, 20, 15, 0.8) 45%, rgba(12, 12, 12, 1) 100%);">
    <div class="relative w-full overflow-hidden py-4 group" @mouseenter="stopAutoScroll" @mouseleave="startAutoScroll">

      <!-- VIEWPORT -->
      <div class="w-full overflow-hidden">

        <!-- TRACK -->
        <div ref="sliderRef" class="flex gap-4 sm:gap-6" :class="{
          'transition-transform duration-700 ease-in-out':
            isTransitioning
        }" @transitionend="handleTransitionEnd">

          <!-- SLIDES -->
          <div v-for="(slide, index) in infiniteSlides" :key="`${slide.id}-${index}`" class="
              shrink-0
              w-[80vw]
              sm:w-[60vw]
              md:w-[50vw]
              max-w-[850px]
              h-[400px]
              sm:h-[500px]
              md:h-[580px]
              relative
              rounded-2xl
              overflow-hidden
              border
              border-slate-800
              bg-[#161616]
            ">

            <!-- IMAGE -->
            <img :src="slide.image" :alt="`Slide ${slide.id}`" class="
                absolute
                inset-0
                w-full
                h-full
                object-cover
                z-0
              " />

            <!-- ACTION BOX -->
            <div class="
                absolute
                bottom-4
                right-4
                z-20
                w-56
                sm:w-64
                p-3
                sm:p-3.5
                bg-black/90
                backdrop-blur-md
                rounded-2xl
                border
                border-white/10
                space-y-3
                shadow-2xl
              ">

              <!-- PROGRESS -->
              <div class="
                  relative
                  w-full
                  h-3.5
                  bg-slate-800/90
                  rounded-full
                  border
                  border-slate-700/60
                  p-0.5
                  flex
                  items-center
                ">
                <div class="
                    h-full
                    bg-gradient-to-r
                    from-[#eec76f]
                    to-amber-500
                    rounded-full
                    transition-all
                    duration-300
                  " :style="{
                    width: `${slide.percentage}%`
                  }"></div>

                <div class="
                    absolute
                    -top-2.5
                    -translate-x-1/2
                    w-8
                    h-8
                    rounded-full
                    bg-[#222]
                    border-2
                    border-[#eec76f]
                    flex
                    items-center
                    justify-center
                    text-[10px]
                    font-extrabold
                    text-white
                    shadow-md
                  " :style="{
                    left: `${slide.percentage}%`
                  }">
                  {{ slide.percentage }}%
                </div>
              </div>

              <!-- CTA -->
              <Link :href="route('competition.detail', slide.id)" class="
                  block
                  w-full
                  py-2.5
                  text-center
                  bg-gradient-to-r
                  from-amber-400
                  via-[#eec76f]
                  to-amber-500
                  hover:brightness-110
                  text-slate-950
                  font-black
                  rounded-xl
                  text-xs
                  sm:text-sm
                  tracking-wide
                  transition
                  shadow-lg
                  active:scale-[0.98]
                  cursor-pointer
                ">
                Enter now
                <span class="font-bold">
                 <!-- {{ slide.title }} {{ slide.price }} -->
                 {{ slide.title }}
                </span>
              </Link>


            </div>
          </div>

        </div>
      </div>

      <!-- PREVIOUS -->
      <button @click="prevSlide" class="
          cursor-pointer
          absolute
          left-2
          sm:left-6
          top-1/2
          -translate-y-1/2
          z-30
          w-10
          h-10
          rounded-full
          bg-slate-900/90
          border
          border-slate-700
          text-white
          flex
          items-center
          justify-center
          hover:bg-[#eec76f]
          hover:text-slate-950
          transition
          opacity-0
          group-hover:opacity-100
          shadow-xl
        " aria-label="Previous Slide">
        &#10094;
      </button>

      <!-- NEXT -->
      <button @click="nextSlide" class="
          cursor-pointer
          absolute
          right-2
          sm:right-6
          top-1/2
          -translate-y-1/2
          z-30
          w-10
          h-10
          rounded-full
          bg-slate-900/90
          border
          border-slate-700
          text-white
          flex
          items-center
          justify-center
          hover:bg-[#eec76f]
          hover:text-slate-950
          transition
          opacity-0
          group-hover:opacity-100
          shadow-xl
        " aria-label="Next Slide">
        &#10095;
      </button>

      <!-- DOTS -->
      <div class="flex justify-center items-center gap-2 pt-4">
        <button v-for="(slide, index) in props.slides" :key="'dot-' + slide.id" @click="goToOriginalSlide(index)" class="
            h-2
            rounded-full
            transition-all
            duration-300
            cursor-pointer
          " :class="currentSlide === index
              ? 'w-8 bg-[#eec76f]'
              : 'w-2 bg-slate-700 hover:bg-slate-500'
            " :aria-label="`Go to slide ${index + 1}`"></button>
      </div>

    </div>
  </section>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar {
  display: none;
}

.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>