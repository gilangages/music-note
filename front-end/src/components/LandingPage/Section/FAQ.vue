<script setup>
import { computed, ref } from "vue"; // Import computed
import { useI18n } from "vue-i18n"; // Import useI18n

const { t } = useI18n(); // Inisialisasi

// --- UBAH DARI REF BIASA KE COMPUTED ---
// Kita bungkus array di dalam computed agar saat bahasa berubah, array ini dibuat ulang
const faqsData = computed(() => [
  {
    question: t("faq.q1"),
    answer: t("faq.a1"),
    open: false,
  },
  {
    question: t("faq.q2"),
    answer: t("faq.a2"),
    open: false,
  },
  {
    question: t("faq.q3"),
    answer: t("faq.a3"),
    open: false,
  },
  {
    question: t("faq.q4"),
    answer: t("faq.a4"),
    open: false,
  },
  {
    question: t("faq.q5"),
    answer: t("faq.a5"),
    open: false,
  },
  {
    question: t("faq.q6"),
    answer: t("faq.a6"),
    open: false,
  },
  {
    question: t("faq.q7"),
    answer: t("faq.a7"),
    open: false,
  },
  {
    question: t("faq.q8"),
    answer: t("faq.a8"),
    open: false,
  },
  {
    question: t("faq.q9"),
    answer: t("faq.a9"),
    open: false,
  },
  {
    question: t("faq.q10"),
    answer: t("faq.a10"),
    open: false,
  },
  {
    question: t("faq.q11"),
    answer: t("faq.a11"),
    open: false,
  },
  {
    question: t("faq.q12"),
    answer: t("faq.a12"),
    open: false,
  },
]);

// --- TRIK PENTING UNTUK STATE 'OPEN' ---
// Karena computed itu 'read-only' (tidak bisa diubah langsung nilai .open = true),
// kita butuh state terpisah untuk menyimpan index mana yang sedang terbuka.
const openIndex = ref(null); // Menyimpan index yang sedang dibuka (misal: 0, 1, atau null)

const toggleFaq = (index) => {
  if (openIndex.value === index) {
    openIndex.value = null; // Tutup jika diklik lagi
  } else {
    openIndex.value = index; // Buka index baru
  }
};
</script>

<template>
  <div class="p-[2em] max-w-4xl mx-auto">
    <h2 class="text-[#e5e5e5] text-[24px] font-semibold text-center mb-2">
      {{ $t("faq.header_title") }}
    </h2>
    <p class="text-[#8c8a8a] text-center mb-10 text-sm">
      {{ $t("faq.header_subtitle") }}
    </p>

    <div class="space-y-4">
      <div
        v-for="(faq, index) in faqsData"
        :key="index"
        class="border border-white/10 rounded-xl overflow-hidden bg-white/[0.02] transition-colors duration-300 hover:border-white/20">
        <button
          @click="toggleFaq(index)"
          class="w-full flex items-center justify-between p-5 text-left focus:outline-none group cursor-pointer">
          <span
            class="text-[#e5e5e5] font-medium transition-colors duration-300 group-hover:text-[#9a203e]"
            :class="{ 'text-[#9a203e]': openIndex === index }">
            {{ faq.question }}
          </span>

          <span
            class="text-white/50 transition-transform duration-300 transform"
            :class="{ 'rotate-180 text-[#9a203e]': openIndex === index }">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path
                fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd" />
            </svg>
          </span>
        </button>

        <div
          class="grid transition-[grid-template-rows] duration-300 ease-out"
          :class="openIndex === index ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'">
          <div class="overflow-hidden">
            <div class="px-5 pb-5 text-[#8c8a8a] text-sm leading-relaxed">
              <span v-html="faq.answer"></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
