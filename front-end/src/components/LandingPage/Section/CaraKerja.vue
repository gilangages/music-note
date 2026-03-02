<script setup>
// Menggunakan vue-i18n untuk dukungan multibahasa
import { useI18n } from "vue-i18n";
const { t } = useI18n();
</script>

<template>
  <section class="py-16 sm:py-24 relative font-jakarta bg-[#0f0505] overflow-hidden">
    <div
      class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-[#9a203e]/5 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 sm:px-12 relative z-10">
      <div class="text-center mb-16 sm:mb-24">
        <h2 class="text-[22px] sm:text-[28px] md:text-[32px] font-bold text-white mb-4">
          {{ $t("cara_kerja.title") }}
        </h2>
        <p class="text-gray-400 max-w-2xl mx-auto text-sm sm:text-base leading-relaxed">
          {{ $t("cara_kerja.subtitle") }}
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-12 md:gap-8 relative">
        <div
          class="hidden md:block absolute top-[2.5rem] left-[16%] right-[16%] h-[1px] bg-gradient-to-r from-transparent via-[#3f3233] to-transparent z-0"></div>

        <div class="relative z-10 flex flex-col items-center text-center group cursor-default">
          <div
            class="w-20 h-20 rounded-2xl bg-[#1c1516] border border-[#3f3233] flex items-center justify-center mb-6 group-hover:border-[#9a203e] group-hover:-translate-y-2 group-hover:shadow-[0_0_25px_rgba(154,32,62,0.25)] transition-all duration-300 relative overflow-hidden">
            <div
              class="absolute inset-0 bg-gradient-to-br from-[#9a203e]/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <span class="text-3xl font-bold text-[#e5e5e5] font-hand group-hover:scale-110 transition-transform">
              1
            </span>
          </div>
          <h3 class="text-lg sm:text-xl font-bold text-white mb-3 group-hover:text-[#9a203e] transition-colors">
            {{ $t("cara_kerja.step1.title") }}
          </h3>
          <p class="text-gray-400 text-xs sm:text-sm leading-relaxed max-w-[250px]">
            {{ $t("cara_kerja.step1.desc") }}
          </p>
        </div>

        <div class="relative z-10 flex flex-col items-center text-center group cursor-default">
          <div
            class="w-20 h-20 rounded-2xl bg-[#1c1516] border border-[#3f3233] flex items-center justify-center mb-6 group-hover:border-[#9a203e] group-hover:-translate-y-2 group-hover:shadow-[0_0_25px_rgba(154,32,62,0.25)] transition-all duration-300 relative overflow-hidden">
            <div
              class="absolute inset-0 bg-gradient-to-br from-[#9a203e]/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <span class="text-3xl font-bold text-[#e5e5e5] font-hand group-hover:scale-110 transition-transform">
              2
            </span>
          </div>
          <h3 class="text-lg sm:text-xl font-bold text-white mb-3 group-hover:text-[#9a203e] transition-colors">
            {{ $t("cara_kerja.step2.title") }}
          </h3>
          <p class="text-gray-400 text-xs sm:text-sm leading-relaxed max-w-[250px]">
            {{ $t("cara_kerja.step2.desc") }}
          </p>
        </div>

        <div class="relative z-10 flex flex-col items-center text-center group cursor-default">
          <div
            class="w-20 h-20 rounded-2xl bg-[#1c1516] border border-[#3f3233] flex items-center justify-center mb-6 group-hover:border-[#9a203e] group-hover:-translate-y-2 group-hover:shadow-[0_0_25px_rgba(154,32,62,0.25)] transition-all duration-300 relative overflow-hidden">
            <div
              class="absolute inset-0 bg-gradient-to-br from-[#9a203e]/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <span class="text-3xl font-bold text-[#e5e5e5] font-hand group-hover:scale-110 transition-transform">
              3
            </span>
          </div>
          <h3 class="text-lg sm:text-xl font-bold text-white mb-3 group-hover:text-[#9a203e] transition-colors">
            {{ $t("cara_kerja.step3.title") }}
          </h3>
          <p class="text-gray-400 text-xs sm:text-sm leading-relaxed max-w-[250px]">
            {{ $t("cara_kerja.step3.desc") }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>
