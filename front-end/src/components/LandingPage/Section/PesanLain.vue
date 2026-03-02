<script setup>
import { onMounted, ref, nextTick } from "vue";
import { noteListGlobal } from "../../../lib/api/NoteApi";
import { alertError } from "../../../lib/alert";
import { formatTime, isEdited } from "../../../lib/dateFormatter";
import { useCardTheme } from "../../../lib/useCardTheme";
import { useNow } from "@vueuse/core";
import Swal from "sweetalert2";
import { computed } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n"; // <--- 1. Import ini

const emit = defineEmits(["loaded"]);
//useTheme
const { getTheme, getSelectedTheme } = useCardTheme();

// --- STATE ---
const notes = ref([]);
const scrollContainer = ref(null);
const currentAudio = ref(new Audio()); // State Audio Player
const currentTime = ref(0);

// Modal & Preview
const showModal = ref(false);
const selectedNote = ref(null);
const isVinylSpinning = ref(false);
const showImagePreview = ref(false);
const previewImageUrl = ref("");

const now = useNow({ interval: 60000 });
const router = useRouter();
const { t, locale } = useI18n();

// Helper computed untuk theme modal
const selectedTheme = computed(() => {
  return getSelectedTheme(selectedNote.value);
});

// --- FETCH DATA ---
async function fetchNoteGlobal() {
  try {
    const response = await noteListGlobal();
    const responseBody = await response.json();

    if (response.ok) {
      notes.value = responseBody.data;
    } else {
      const pesanError = responseBody.errors ? Object.values(responseBody.errors)[0][0] : responseBody.message;
      await alertError(pesanError);
    }
  } catch (error) {
    console.error(error);
  } finally {
    emit("loaded");
  }
}

// --- LOGIC PLAY AUDIO (DIPUSATKAN DISINI) ---
const playAudio = (item) => {
  // Reset audio player state
  currentAudio.value.pause();
  currentAudio.value.currentTime = 0;

  let streamUrl = null;

  // Cek Priority: ID (Proxy Backend) -> Preview URL (Fallback)
  if (item.music_track_id) {
    streamUrl = `${import.meta.env.VITE_APP_PATH}/stream/${item.music_track_id}`;
  } else if (item.music_preview_url) {
    streamUrl = item.music_preview_url;
  }

  if (streamUrl) {
    currentAudio.value.src = streamUrl;
    currentAudio.value.volume = 0.5;
    currentAudio.value.loop = true;

    // Set listener update waktu
    currentAudio.value.ontimeupdate = () => {
      currentTime.value = currentAudio.value.currentTime;
    };

    // Play
    console.log("Memutar:", streamUrl);
    currentAudio.value.play().catch((e) => console.error("Gagal play audio:", e));
  }
};

// --- FUNGSI REPLY ---
const handleReplyClick = () => {
  // PERBAIKAN: JANGAN panggil closeModalDetail() disini.
  // Biarkan modal tetap terbuka di background SweetAlert.

  Swal.fire({
    title: t("pesan_lain.modal.alert_title"), // <--- Ganti string manual
    text: t("pesan_lain.modal.alert_text"), // <--- Ganti string manual
    icon: "info",
    showCancelButton: true,
    confirmButtonColor: "#9a203e",
    cancelButtonColor: "#333",
    confirmButtonText: t("pesan_lain.modal.alert_confirm"),
    cancelButtonText: t("pesan_lain.modal.alert_cancel"),
    // --- TAMBAHAN PENTING: Fix Z-Index ---
    didOpen: () => {
      const container = Swal.getContainer();
      if (container) {
        // Paksa z-index Alert jadi 100.000 (di atas modal yang 9.999)
        container.style.zIndex = "100000";
      }
    },
  }).then((result) => {
    if (result.isConfirmed) {
      // Jika user mau login, baru kita tutup modalnya (opsional, router push biasanya otomatis handle)
      closeModalDetail();
      router.push("/login");
    }
    // Jika "Nanti dulu", kita tidak perlu melakukan apa-apa (modal tetap terbuka & musik tetap jalan)
  });
};

// --- SCROLL LOGIC ---
const scroll = (direction) => {
  if (scrollContainer.value) {
    const scrollAmount = 450;
    if (direction === "left") {
      scrollContainer.value.scrollBy({ left: -scrollAmount, behavior: "smooth" });
    } else {
      scrollContainer.value.scrollBy({ left: scrollAmount, behavior: "smooth" });
    }
  }
};

// --- MODAL LOGIC ---
const openModalDetail = (note) => {
  selectedNote.value = note;
  showModal.value = true;
  currentTime.value = 0;

  // PERBAIKAN: Panggil playAudio SEKALI SAJA.
  // Hapus duplikasi logika audio yang sebelumnya ada disini.
  playAudio(note);

  nextTick(() => {
    setTimeout(() => {
      isVinylSpinning.value = true;
    }, 300);
  });
};

const closeModalDetail = () => {
  isVinylSpinning.value = false;

  // Stop Audio & Reset
  currentAudio.value.pause();
  currentAudio.value.currentTime = 0;
  currentTime.value = 0;
  currentAudio.value.loop = false;
  currentAudio.value.ontimeupdate = null; // Bersihkan listener

  setTimeout(() => {
    showModal.value = false;
    selectedNote.value = null; // Ini yang bikin gambar hilang kalau dipanggil sembarangan
  }, 100);
};

// --- IMAGE PREVIEW ---
const openPreview = (url) => {
  if (url) {
    previewImageUrl.value = url;
    showImagePreview.value = true;
  }
};
const closePreview = () => {
  showImagePreview.value = false;
  setTimeout(() => {
    previewImageUrl.value = "";
  }, 300);
};

// --- FORMATTER ---
const formatDateDetail = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  const optionsDate = { weekday: "long", day: "numeric", month: "short", year: "numeric" };
  const hours = String(date.getHours()).padStart(2, "0");
  const minutes = String(date.getMinutes()).padStart(2, "0");

  // --- PERBAIKAN DI SINI ---
  // Cek locale yang sedang aktif dari i18n
  // Jika 'en' gunakan 'en-US', jika 'id' gunakan 'id-ID'
  const currentLang = locale.value === "en" ? "en-US" : "id-ID";

  // Gunakan variable currentLang, JANGAN "id-ID" langsung
  return `${date.toLocaleDateString(currentLang, optionsDate)} • ${hours}:${minutes} WIB`;
};

const formatTimeMusic = (time) => {
  if (!time || isNaN(time)) return "0:00";
  const minutes = Math.floor(time / 60);
  const seconds = Math.floor(time % 60);
  return `${minutes}:${seconds < 10 ? "0" : ""}${seconds}`;
};

onMounted(async () => {
  await fetchNoteGlobal();
});
</script>

<template>
  <div class="mt-[4em] relative font-jakarta">
    <div class="flex justify-between items-center text-[#e5e5e5] mx-[2em] mb-[1em]">
      <h2 class="text-[18px] sm:text-[20px] font-semibold">{{ $t("pesan_lain.header_title") }}</h2>
      <RouterLink
        to="/login"
        class="hidden sm:flex gap-1 items-center cursor-pointer hover:opacity-80 decoration-0 no-underline">
        <span class="uppercase text-[12px] font-semibold text-[#e5e5e5] hover:underline">
          {{ $t("pesan_lain.view_all") }}
        </span>
        <img src="../../../assets/img/arrow-right.svg" class="w-[14px]" />
      </RouterLink>
    </div>

    <div class="relative group px-4 sm:px-12">
      <button
        @click="scroll('left')"
        class="hidden sm:flex absolute left-2 top-1/2 -translate-y-1/2 z-20 bg-[#1c1516] p-2 rounded-full cursor-pointer hover:bg-[#9a203e] border border-[#3f3233] shadow-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke-width="2"
          stroke="currentColor"
          class="w-6 h-6 text-white">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
        </svg>
      </button>

      <div
        ref="scrollContainer"
        class="flex gap-[1.5em] overflow-x-auto pb-8 pt-4 snap-x snap-mandatory scrollbar-hide px-2">
        <div
          v-for="(note, index) in notes"
          :key="`${note.id || index}-${locale}`"
          @click="openModalDetail(note)"
          class="min-w-[85vw] sm:min-w-[450px] snap-center group/card cursor-pointer flex flex-col h-full">
          <div
            :class="[getTheme(note.id).bg, getTheme(note.id).border, getTheme(note.id).hover]"
            class="rounded-[24px] p-6 border shadow-lg transition-all duration-300 hover:-translate-y-2 relative overflow-hidden h-full flex flex-col">
            <div
              :class="`bg-gradient-to-b ${getTheme(note.id).gradient} to-transparent`"
              class="absolute inset-0 opacity-0 group-hover/card:opacity-100 transition-opacity duration-500"></div>

            <div class="mb-5 relative z-10">
              <p class="text-[11px] text-[#666] font-bold uppercase tracking-wider mb-1">
                {{ $t("pesan_lain.card.to") }}
              </p>
              <h2
                :class="getTheme(note.id).text_hover"
                class="text-2xl font-bold text-white transition-colors truncate">
                {{ note.recipient }}
              </h2>
            </div>

            <div class="flex gap-4 items-center relative z-10 mb-5">
              <div
                class="w-14 h-14 rounded-[12px] overflow-hidden shrink-0 border border-[#333] shadow-md group-hover/card:scale-105 transition-transform bg-black">
                <img :src="note.music_album_image" class="w-full h-full object-cover" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-white truncate">{{ note.music_track_name }}</p>
                <p class="text-xs text-[#888] truncate">{{ note.music_artist_name }}</p>
              </div>
            </div>

            <div
              :class="[getTheme(note.id).border, `group-hover/card:border-${getTheme(note.id).id}-500/50`]"
              class="bg-black/20 rounded-[16px] p-4 border mb-4 transition-colors relative z-10">
              <p
                v-text="'&quot;' + note.content + '&quot;'"
                class="text-[15px] text-[#ccc] italic font-hand leading-relaxed whitespace-pre-wrap line-clamp-3 break-words"></p>
            </div>

            <div :class="getTheme(note.id).border" class="flex flex-col gap-3 pt-4 border-t relative z-10 mt-auto">
              <div class="flex items-center gap-2">
                <img
                  :src="note.author_avatar || note.author_photo_url"
                  class="w-6 h-6 rounded-full border border-[#333] object-cover" />
                <div class="flex flex-col">
                  <span class="text-[10px] text-[#666] uppercase font-bold">{{ $t("pesan_lain.card.from") }}</span>
                  <div class="flex items-center gap-1.5">
                    <span class="text-xs text-[#999] font-medium leading-none">{{ note.author_name }}</span>
                    <span
                      v-if="note.is_admin"
                      class="bg-[#9a203e] text-white text-[9px] px-1.5 py-0.5 rounded-[4px] font-bold uppercase tracking-wider border border-white/10 shadow-[0_0_10px_rgba(154,32,62,0.6)]">
                      {{ $t("pesan_lain.card.admin") }}
                    </span>
                  </div>
                </div>

                <div class="ml-auto flex items-center gap-3">
                  <span class="text-[10px] text-[#555] font-mono">
                    {{ formatTime(note.created_at, now) }}
                    <span
                      v-if="isEdited(note.created_at, note.updated_at)"
                      :class="getTheme(note.id).text"
                      class="italic ml-1 block sm:inline">
                      {{ $t("pesan_lain.card.edited") }}
                    </span>
                  </span>

                  <div
                    :class="getTheme(note.id).text_hover"
                    class="text-[#e5e5e5] transition-colors transform group-hover/card:translate-x-1 duration-300">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2.5"
                      stroke-linecap="round"
                      stroke-linejoin="round">
                      <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <button
        @click="scroll('right')"
        class="hidden sm:flex absolute right-2 top-1/2 -translate-y-1/2 z-20 bg-[#1c1516] p-2 rounded-full cursor-pointer hover:bg-[#9a203e] border border-[#3f3233] shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke-width="2"
          stroke="currentColor"
          class="w-6 h-6 text-white">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
        </svg>
      </button>
    </div>

    <div class="flex justify-end mr-[2em] sm:hidden mt-2 mb-10">
      <RouterLink to="/login" class="flex gap-1 items-center cursor-pointer hover:opacity-80 decoration-0 no-underline">
        <span class="uppercase text-[10px] font-semibold text-[#e5e5e5] hover:underline">
          {{ $t("pesan_lain.view_all") }}
        </span>
        <img src="../../../assets/img/arrow-right.svg" class="w-[12px]" />
      </RouterLink>
    </div>

    <Teleport to="body">
      <Transition name="fade">
        <div
          v-if="showModal"
          class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/90 backdrop-blur-md p-4"
          @click.self="closeModalDetail">
          <div
            class="w-full max-w-[420px] md:max-w-[600px] rounded-[24px] shadow-2xl border flex flex-col overflow-hidden relative max-h-[90vh] transition-transform duration-300"
            :class="[showModal ? 'scale-100' : 'scale-95', selectedTheme.bg, selectedTheme.border]">
            <button
              @click="closeModalDetail"
              :class="selectedTheme.btn_hover"
              class="absolute top-4 right-4 z-50 bg-black/40 text-white p-2 rounded-full transition-colors backdrop-blur-md border border-white/10 cursor-pointer">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>

            <div
              class="relative p-6 pt-10 border-b flex flex-col items-center shrink-0 overflow-hidden"
              :class="selectedTheme.border">
              <div
                class="absolute inset-0 opacity-40 pointer-events-none bg-gradient-to-b to-transparent"
                :class="selectedTheme.gradient"></div>

              <div class="relative z-10 w-full flex flex-col items-center">
                <div
                  class="w-[160px] h-[160px] rounded-full bg-[#111] border-4 border-[#1c1516] flex items-center justify-center relative mb-5 transition-transform duration-[8s] ease-linear"
                  :class="[isVinylSpinning ? 'animate-spin-slow' : '', selectedTheme.shadow]">
                  <div
                    class="absolute inset-0 rounded-full border-[2px] border-[#222] opacity-50 transform scale-90"></div>
                  <img
                    :src="selectedNote?.music_album_image"
                    class="w-[80px] h-[80px] rounded-full object-cover border-2 border-[#111] relative z-10" />
                </div>

                <h2 class="text-xl font-bold text-white text-center leading-tight">
                  {{ selectedNote?.music_track_name }}
                </h2>
                <p :class="selectedTheme.text" class="text-xs font-medium uppercase tracking-wide mb-4 mt-1">
                  {{ selectedNote?.music_artist_name }}
                </p>

                <div class="w-full max-w-[200px] mb-5 mt-2">
                  <div class="h-1 bg-black/40 rounded-full overflow-hidden w-full">
                    <div
                      :class="selectedTheme.bg_color"
                      class="h-full transition-all duration-100 ease-linear"
                      :style="{ width: `${(currentTime / 30) * 100}%` }"></div>
                  </div>
                  <div class="flex justify-between text-[10px] text-white/50 mt-1 font-mono">
                    <span>{{ $t("pesan_lain.modal.preview") }}: {{ formatTimeMusic(currentTime) }}</span>
                    <span>0:30</span>
                  </div>
                </div>

                <a
                  v-if="selectedNote?.music_track_id"
                  :href="`https://www.deezer.com/track/${selectedNote?.music_track_id}`"
                  target="_blank"
                  :class="selectedTheme.modal_btn"
                  class="flex items-center gap-2 text-white px-5 py-2.5 rounded-full text-xs font-bold transition-transform hover:scale-105 no-underline decoration-0 group">
                  <img
                    src="https://cdn.brandfetch.io/idEUKgCNtu/theme/dark/symbol.svg?c=1dxbfHSJFAPEGdCLU4o5B"
                    alt="Deezer"
                    class="w-4 h-4 object-contain filter brightness-0 invert" />
                  <span>{{ $t("pesan_lain.modal.play_full") }}</span>
                </a>
              </div>
            </div>

            <div class="flex-1 bg-black/20 p-6 overflow-y-auto custom-scrollbar">
              <div class="flex justify-between items-center mb-6 pb-4 border-b" :class="selectedTheme.border">
                <div class="flex items-center gap-3">
                  <div
                    @click.stop="openPreview(selectedNote?.author_avatar || selectedNote?.author_photo_url)"
                    class="relative group/avatar cursor-zoom-in">
                    <img
                      :src="selectedNote?.author_avatar || selectedNote?.author_photo_url"
                      class="w-10 h-10 rounded-full border border-white/10 object-cover transition-transform group-hover/avatar:scale-110" />
                  </div>
                  <div>
                    <p class="text-[10px] text-white/50 uppercase tracking-wide">
                      {{ $t("pesan_lain.modal.from_label") }}
                    </p>
                    <div class="flex items-center gap-2">
                      <p class="text-sm font-bold text-white">{{ selectedNote?.author_name }}</p>
                      <span
                        v-if="selectedNote?.is_admin"
                        class="bg-[#9a203e] text-white text-[9px] px-1.5 py-0.5 rounded-[4px] font-bold uppercase tracking-wider border border-white/10">
                        Admin
                      </span>
                    </div>
                  </div>
                </div>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="20"
                  height="20"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  class="text-white/30"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round">
                  <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
                <div class="text-right">
                  <p class="text-[10px] text-white/50 uppercase tracking-wide">{{ $t("pesan_lain.modal.to_label") }}</p>
                  <p :class="selectedTheme.text" class="text-sm font-bold">{{ selectedNote?.recipient }}</p>
                </div>
              </div>

              <div class="mb-6">
                <p class="font-hand text-xl text-[#e5e5e5] leading-loose tracking-wide whitespace-pre-wrap break-words">
                  "{{ selectedNote?.content }}"
                </p>
              </div>

              <div
                class="flex items-center gap-2 text-[11px] text-white/60 font-mono bg-black/20 p-3 rounded-lg border mb-6"
                :class="selectedTheme.border">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="14"
                  height="14"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>
                  {{ $t("pesan_lain.modal.sent_at") }} {{ formatDateDetail(selectedNote?.created_at) }}
                  <span
                    v-if="isEdited(selectedNote?.created_at, selectedNote?.updated_at)"
                    :class="selectedTheme.text"
                    class="italic ml-1 font-bold">
                    {{ $t("pesan_lain.card.edited") }}
                  </span>
                </span>
              </div>

              <div class="border-t border-white/10 pt-6">
                <div class="flex items-center justify-between mb-4 gap-2">
                  <h3
                    class="text-[#e5e5e5] text-xs sm:text-sm font-bold uppercase tracking-wide sm:tracking-widest flex items-center gap-1.5 sm:gap-2 shrink-0">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2">
                      <path d="M9 18l6-6-6-6" />
                    </svg>
                    <span class="truncate">
                      {{ $t("pesan_lain.modal.replies_title") }} ({{ selectedNote?.replies?.length || 0 }})
                    </span>
                  </h3>

                  <button
                    @click="handleReplyClick"
                    class="text-[10px] sm:text-xs bg-white/10 hover:bg-white/20 text-white px-2.5 py-1.5 sm:px-3 sm:py-1.5 rounded-full transition-colors font-semibold whitespace-nowrap shrink-0">
                    {{ $t("pesan_lain.modal.reply_btn") }}
                  </button>
                </div>

                <div class="flex flex-col gap-3 max-h-[200px] overflow-y-auto custom-scrollbar">
                  <div
                    v-if="!selectedNote?.replies || selectedNote.replies.length === 0"
                    class="text-center py-4 text-white/20 text-xs italic rounded-lg">
                    {{ $t("pesan_lain.modal.no_replies") }}
                  </div>

                  <div
                    v-for="reply in selectedNote?.replies"
                    :key="reply.id"
                    class="group/reply flex items-center gap-3 bg-black/20 p-3 rounded-xl border border-white/5 hover:border-white/10 transition-colors">
                    <div class="relative w-10 h-10 shrink-0">
                      <img
                        :src="reply.music_album_image"
                        class="w-full h-full rounded-md object-cover brightness-75 hover:brightness-100 transition-all" />
                      <button
                        @click="playAudio(reply)"
                        class="absolute inset-0 flex items-center justify-center text-white opacity-0 group-hover/reply:opacity-100 transition-opacity">
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="20"
                          height="20"
                          viewBox="0 0 24 24"
                          fill="currentColor">
                          <path d="M8 5v14l11-7z" />
                        </svg>
                      </button>
                    </div>

                    <div class="flex-1 min-w-0">
                      <div class="flex justify-between items-start">
                        <p class="text-xs font-bold text-white truncate pr-2">{{ reply.music_track_name }}</p>
                        <span class="text-[9px] text-white/30 whitespace-nowrap">
                          {{ formatTime(reply.created_at, now) }}
                        </span>
                      </div>
                      <p class="text-[10px] font-medium text-white/50 truncate">
                        {{ reply.music_artist_name }}
                      </p>
                      <p v-if="reply.content" class="text-[10px] text-white/60 italic truncate mt-0.5">
                        "{{ reply.content }}"
                      </p>
                      <p class="text-[9px] text-white/30 mt-1">
                        {{ $t("pesan_lain.modal.from_label") }}:
                        {{ reply.author_name || $t("pesan_lain.modal.anonymous") }}
                      </p>
                    </div>
                  </div>

                  <div v-if="selectedNote?.replies?.length >= 5" class="text-center py-4">
                    <p class="text-[10px] text-white/30 italic">
                      {{ $t("pesan_lain.modal.replies_limit_1") }}
                      <span class="block">{{ $t("pesan_lain.modal.replies_limit_2") }}</span>
                    </p>
                  </div>
                </div>
              </div>

              <div class="mt-6">
                <button
                  @click="closeModalDetail"
                  :class="[
                    selectedTheme.btn_hover,
                    'border-white/10 text-white/50',
                    'hover:text-white hover:border-transparent',
                  ]"
                  class="w-full py-3 rounded-[12px] border font-bold text-xs uppercase tracking-widest transition-all cursor-pointer">
                  {{ $t("pesan_lain.modal.close") }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <Teleport to="body">
      <Transition name="fade">
        <div
          v-if="showImagePreview"
          class="fixed inset-0 z-[10000] flex flex-col items-center justify-center bg-black/95 backdrop-blur-xl p-4 cursor-pointer"
          @click="closePreview">
          <div class="relative flex flex-col items-center w-full max-w-[90vw] max-h-[90vh] cursor-default">
            <button
              @click.stop="closePreview"
              class="absolute -top-12 right-0 text-white/50 hover:text-white transition-colors p-2 z-50">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="32"
                height="32"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
            <img
              :src="previewImageUrl"
              class="w-auto h-auto max-w-full max-h-[80vh] object-contain rounded-lg shadow-2xl"
              @click.stop />
            <p class="text-white/50 text-sm tracking-widest uppercase font-bold mt-4" @click.stop>
              {{ $t("pesan_lain.preview.profile_photo") }}
            </p>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped></style>
