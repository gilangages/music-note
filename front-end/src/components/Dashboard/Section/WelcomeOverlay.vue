<script setup>
// 1. Tambahkan 'watch' di import
import { ref, watch, defineEmits, onMounted } from "vue";
import { userState } from "../../../lib/store";
import { useI18n } from "vue-i18n"; // Import i18n

const { t } = useI18n(); // Inisialisasi
const emit = defineEmits(["animation-complete"]);

const displayDetail = ref("");
const isTextVisible = ref(false);
const showIntro = ref(true);

// ... (kode typeWriter dan wait biarkan sama) ...

const typeWriter = async (text, speed = 50) => {
  // ... (kode sama)
  displayDetail.value = "";
  let i = 0;
  while (i < text.length) {
    if (text[i] === "<") {
      const closingBracket = text.indexOf(">", i);
      if (closingBracket !== -1) {
        displayDetail.value += text.substring(i, closingBracket + 1);
        i = closingBracket + 1;
        continue;
      }
    }
    displayDetail.value += text[i];
    let currentSpeed = speed;
    if ([",", ".", "?", "^"].includes(text[i])) currentSpeed = speed * 4;
    await new Promise((resolve) => setTimeout(resolve, currentSpeed));
    i++;
  }
};

const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

async function checkAnimationRequirement() {
  const currentNameInMemory = userState.value?.name;

  // Jika nama belum ada, jangan lakukan apa-apa (tunggu watcher)
  if (!currentNameInMemory) {
    // showIntro.value = true;
    return;
  }

  const storedAnimName = sessionStorage.getItem("last_anim_name");

  if (storedAnimName !== currentNameInMemory) {
    await handleAnimation(currentNameInMemory);
  } else {
    showIntro.value = false;
    emit("animation-complete");
  }
}

// ... (kode handleAnimation biarkan sama, hanya teks diganti t()) ...
async function handleAnimation(userName) {
  showIntro.value = true;
  isTextVisible.value = true;

  // Menggunakan i18n dengan parameter {name} untuk interpolasi nama
  const welcomeText = t("welcome_overlay.greeting", { name: userName });

  await typeWriter(welcomeText, 50);
  await wait(400);
  isTextVisible.value = false;
  await wait(300);

  isTextVisible.value = true;
  await typeWriter(t("welcome_overlay.question"), 50);
  await wait(400);
  isTextVisible.value = false;
  await wait(300);

  isTextVisible.value = true;
  await typeWriter(t("welcome_overlay.instruction"), 40);
  await wait(500);

  isTextVisible.value = false;
  await wait(500);

  showIntro.value = false;
  sessionStorage.setItem("last_anim_name", userName);
  emit("animation-complete");
}

// 2. Tambahkan Watcher ini
// Ini berfungsi: "Hei Vue, pantau terus userState.name. Kalau nilainya berubah (misal dari kosong jadi 'Gilang'), jalankan checkAnimationRequirement lagi."
watch(
  () => userState.value.name,
  (newVal) => {
    if (newVal) {
      checkAnimationRequirement();
    }
  },
);

onMounted(() => {
  // Cek awal (berjaga-jaga jika data sudah ada di cache/store)
  checkAnimationRequirement();
});
</script>

<template>
  <Transition name="fade">
    <div
      v-if="showIntro"
      class="fixed inset-0 z-[60] flex items-center justify-center bg-[#0f0505]/95 backdrop-blur-md">
      <div class="text-center px-6">
        <Transition name="text-fade">
          <p
            v-if="isTextVisible"
            v-html="displayDetail"
            class="text-[16px] text-white font-medium leading-relaxed max-w-[300px] italic"></p>
        </Transition>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
.text-fade-enter-active,
.text-fade-leave-active {
  transition: opacity 0.3s ease;
}
.text-fade-enter-from,
.text-fade-leave-to {
  opacity: 0;
}
</style>
