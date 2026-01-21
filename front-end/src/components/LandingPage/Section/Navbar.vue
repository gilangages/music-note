<script setup>
import { ref } from "vue";
import { useRouter, useRoute } from "vue-router";
import LanguageSwitcher from "../../LanguageSwitcher.vue"; // Pastikan path import benar

const router = useRouter();
const route = useRoute();
const isMobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

// Fungsi Scroll dengan Offset
const handleScroll = (sectionId) => {
  const element = document.getElementById(sectionId);
  if (element) {
    const headerOffset = 90;
    const elementPosition = element.getBoundingClientRect().top;
    const offsetPosition = elementPosition + window.scrollY - headerOffset;

    window.scrollTo({
      top: offsetPosition,
      behavior: "smooth",
    });
  }
};

const scrollToSection = async (sectionId) => {
  isMobileMenuOpen.value = false;

  if (route.path !== "/") {
    await router.push("/");
    let attempts = 0;
    const checkExist = setInterval(() => {
      const element = document.getElementById(sectionId);
      if (element) {
        handleScroll(sectionId);
        clearInterval(checkExist);
      }
      attempts++;
      if (attempts >= 20) {
        clearInterval(checkExist);
      }
    }, 100);
  } else {
    handleScroll(sectionId);
  }
};
</script>

<template>
  <div class="font-poppins bg-[#180808] sticky top-0 z-50 w-full">
    <div class="flex items-center justify-between p-[12px] bg-[#180808] relative z-50 h-[63px]">
      <div class="flex items-center gap-4 z-20 relative">
        <button
          @click="toggleMobileMenu"
          class="md:hidden text-[#e5e5e5] hover:text-[#9a203e] transition-transform active:scale-90 focus:outline-none">
          <svg
            v-if="!isMobileMenuOpen"
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <div @click="scrollToSection('home')" class="cursor-pointer flex items-center gap-2 select-none">
          <h1
            class="text-[24px] sm:text-[24px] text-[#9a203e] m-0 sm:ml-[1em] font-bold hover:text-[#b92b4a] transition-colors">
            Resonate
          </h1>
        </div>
      </div>

      <div
        class="hidden md:flex items-center gap-8 text-[14px] font-medium text-gray-400 absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2">
        <button @click="scrollToSection('fitur')" class="hover:text-white transition-colors hover:scale-105 transform">
          {{ $t("nav.features") }}
        </button>

        <button
          @click="scrollToSection('pesan-lain')"
          class="hover:text-white transition-colors hover:scale-105 transform">
          {{ $t("nav.explore") }}
        </button>

        <button @click="scrollToSection('faq')" class="hover:text-white transition-colors hover:scale-105 transform">
          {{ $t("nav.faq") }}
        </button>
      </div>

      <div class="flex items-center gap-[10px] z-20 relative">
        <LanguageSwitcher class="hidden sm:flex mr-2" />

        <RouterLink
          to="/register"
          class="cursor-pointer hidden sm:block text-[#e5e5e5] text-[14px] font-semibold hover:text-[#ff4d6d] transition-colors">
          {{ $t("nav.register") }}
        </RouterLink>
        <RouterLink
          to="/login"
          class="cursor-pointer bg-[#180808] transition-all duration-200 ease-out hover:scale-105 border border-[#9a203e] hover:bg-[#9a203e] text-[#e5e5e5] text-[14px] font-semibold px-[16px] py-[8px] rounded-full shadow-[0_0_10px_rgba(154,32,62,0.2)] hover:shadow-[0_0_20px_rgba(154,32,62,0.4)]">
          {{ $t("nav.login") }}
        </RouterLink>
      </div>
    </div>

    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="transform -translate-y-5 opacity-0"
      enter-to-class="transform translate-y-0 opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="transform translate-y-0 opacity-100"
      leave-to-class="transform -translate-y-5 opacity-0">
      <div
        v-if="isMobileMenuOpen"
        class="md:hidden absolute top-full left-0 w-full bg-[#1c1516] border-b border-[#9a203e]/30 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.8)] z-40 -mt-[1px]">
        <div class="flex flex-col p-6 gap-4 text-[#e5e5e5] text-[15px] font-medium">
          <div class="flex justify-between items-center border-b border-white/5 pb-2 mb-2">
            <span>{{ $t("nav.language") }}</span>
            <LanguageSwitcher />
          </div>
          <button
            @click="scrollToSection('fitur')"
            class="text-left hover:text-[#9a203e] py-2 border-b border-white/5 transition-colors">
            {{ $t("nav.features") }}
          </button>
          <button
            @click="scrollToSection('pesan-lain')"
            class="text-left hover:text-[#9a203e] py-2 border-b border-white/5 transition-colors">
            {{ $t("nav.explore") }}
          </button>
          <button
            @click="scrollToSection('faq')"
            class="text-left hover:text-[#9a203e] py-2 border-b border-white/5 transition-colors">
            {{ $t("nav.faq") }}
          </button>
          <div class="pt-2">
            <RouterLink
              to="/register"
              @click="isMobileMenuOpen = false"
              class="block w-full text-center bg-[#9a203e]/10 text-[#9a203e] font-bold py-3 rounded-xl border border-[#9a203e]/50 hover:bg-[#9a203e] hover:text-white transition-all">
              {{ $t("nav.register") }}
            </RouterLink>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>
