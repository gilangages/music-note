<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";

const { locale } = useI18n();

const switchLanguage = (newLang) => {
  if (locale.value === newLang) return;
  locale.value = newLang;
  localStorage.setItem("user-locale", newLang);
  document.querySelector("html").setAttribute("lang", newLang);
};

const currentLang = computed(() => locale.value);
</script>

<template>
  <div class="flex items-center gap-1">
    <button
      @click="switchLanguage('id')"
      class="text-sm transition-all duration-300 font-jakarta relative px-1"
      :class="
        currentLang === 'id' ? 'text-white font-bold tracking-wide' : 'text-[#8c8a8a] font-medium hover:text-[#b92b4a]'
      ">
      ID
      <span
        v-if="currentLang === 'id'"
        class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-[#9a203e] rounded-full shadow-[0_0_8px_#9a203e]"></span>
    </button>

    <span class="text-[#2c2021] text-sm select-none">/</span>

    <button
      @click="switchLanguage('en')"
      class="text-sm transition-all duration-300 font-jakarta relative px-1"
      :class="
        currentLang === 'en' ? 'text-white font-bold tracking-wide' : 'text-[#8c8a8a] font-medium hover:text-[#b92b4a]'
      ">
      EN
      <span
        v-if="currentLang === 'en'"
        class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-[#9a203e] rounded-full shadow-[0_0_8px_#9a203e]"></span>
    </button>
  </div>
</template>

<style scoped>
/* Pastikan font terload */
.font-jakarta {
  font-family: "Plus Jakarta Sans", sans-serif;
}
</style>
