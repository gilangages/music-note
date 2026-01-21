<script setup></script>
<template>
  <div
    class="flex flex-col items-center justify-between gap-[20px] p-[2em] mt-[2em] text-[#e5e5e5] sm:flex-row sm:mt-[-1em] lg:mt-[-16px] max-w-7xl mx-auto">
    <div class="flex flex-col gap-[10px] justify-center flex-1 w-full text-center sm:text-left">
      <h2 class="text-[20px] font-semibold m-0">{{ $t("hero.highlight") }}</h2>
      <h1 class="text-[28px] m-0 sm:text-[26px] lg:text-[36px] font-semibold leading-tight">
        {{ $t("hero.title") }}
      </h1>

      <div class="mt-4">
        <RouterLink to="/login">
          <button
            class="cursor-pointer bg-[#9a203e] text-[#e5e5e5] font-semibold tracking-[2px] px-[24px] py-[16px] rounded-[10px] border border-transparent transition-transform transition-colors duration-200 ease-out hover:text-[#9a203e] hover:bg-[#0F0505] hover:border-[#9a203e] hover:scale-105">
            {{ $t("hero.cta") }}
          </button>
        </RouterLink>
      </div>
    </div>

    <div class="mt-[3em] sm:mt-0 sm:mr-[1em] flex-1 flex justify-center sm:justify-end shrink-0 w-full sm:w-auto">
      <img
        src="/note.svg"
        alt="Hero Image"
        class="w-full max-w-[320px] md:max-w-[500px] lg:max-w-[600px] object-contain h-auto" />
    </div>
  </div>
</template>

<style scoped>
/* Tambahkan di Hero.vue bagian style */
img {
  animation: float 6s ease-in-out infinite;
}

@keyframes float {
  0% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-20px);
  }
  100% {
    transform: translateY(0px);
  }
}
</style>
