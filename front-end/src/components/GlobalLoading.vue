<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  isLoading: {
    type: Boolean,
    default: true,
  }
});

const progress = ref(0);
const isVisible = ref(props.isLoading);
let interval = null;

const startProgress = () => {
  progress.value = 0;
  if (interval) clearInterval(interval);
  interval = setInterval(() => {
    // Naikkan persentase progresif secara acak agar terlihat berjalan dinamis
    if (progress.value < 85) {
      progress.value += Math.random() * 4 + 1;
    } else if (progress.value < 99) {
      progress.value += Math.random() * 1.5;
    }
    
    if (progress.value > 99) {
      progress.value = 99;
    }
  }, 150);
};

watch(() => props.isLoading, (newVal) => {
  if (newVal) {
    isVisible.value = true;
    startProgress();
  } else {
    if (interval) clearInterval(interval);
    
    // Melesat ke 100% dan delay sejenak sebelum komponen dihancurkan
    const fillInterval = setInterval(() => {
      progress.value += 3;
      if (progress.value >= 100) {
        progress.value = 100;
        clearInterval(fillInterval);
        setTimeout(() => {
          isVisible.value = false;
        }, 500);
      }
    }, 15);
  }
});

onMounted(() => {
  if (props.isLoading) {
    startProgress();
  }
});

onUnmounted(() => {
  if (interval) clearInterval(interval);
});
</script>

<template>
  <Transition name="fade-loader">
    <div
      v-if="isVisible"
      class="fixed inset-0 z-[999999] bg-[#0f0505] flex flex-col items-center justify-center">
      <div class="relative flex items-center justify-center">
        <div
          class="absolute w-20 h-20 border-4 border-[#3f3233] border-t-[#9a203e] border-b-[#9a203e] rounded-full animate-spin"></div>

        <div class="w-12 h-12 flex items-center justify-center bg-[#0f0505] rounded-full z-10 p-2 animate-pulse">
          <img src="/resonate.svg" alt="Resonate Logo" class="w-full h-full object-contain" />
        </div>
      </div>

      <h2 class="mt-8 text-[#9a203e] font-black tracking-widest text-2xl font-mono tabular-nums transition-all">
        {{ Math.floor(progress) }}%
      </h2>



      <!-- Teks Original -->
      <h2 class="mt-2 text-[#e5e5e5] font-bold tracking-widest text-sm animate-pulse uppercase">
        {{ $t("loading.title") }}
      </h2>

      <p class="mt-3 text-gray-500 text-[11px] sm:text-xs max-w-xs text-center px-4 leading-relaxed font-medium">
        {{ $t("loading.subtitle") }}
        <br />
        <span class="text-[#9a203e]/70 text-[10px] block mt-1">
          {{ $t("loading.server_notice") }}
        </span>
      </p>
    </div>
  </Transition>
</template>

<style scoped>
.fade-loader-enter-active,
.fade-loader-leave-active {
  transition: opacity 0.6s ease;
}
.fade-loader-enter-from,
.fade-loader-leave-to {
  opacity: 0;
}
</style>
