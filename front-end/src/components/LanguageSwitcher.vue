<script setup>
import { useI18n } from "vue-i18n";

const { locale } = useI18n();

const switchLanguage = () => {
  const newLocale = locale.value === "id" ? "en" : "id";
  locale.value = newLocale;
  localStorage.setItem("user-locale", newLocale); // Simpan preferensi
};
</script>

<template>
  <button
    @click="switchLanguage"
    class="flex items-center gap-2 px-3 py-1 text-[12px] sm:text-[14px] font-medium text-[#cdcccc] border border-[#9a203e]/50 rounded-full hover:bg-[#9a203e] hover:text-white transition-all duration-300"
    title="Ganti Bahasa / Switch Language">
    <span :class="{ 'text-white font-bold': locale === 'id', 'opacity-50': locale !== 'id' }">ID</span>
    <span class="opacity-30">|</span>
    <span :class="{ 'text-white font-bold': locale === 'en', 'opacity-50': locale !== 'en' }">EN</span>
  </button>
</template>
