<script setup>
import { onMounted, reactive, ref } from "vue";
import { userLogin, sendAppeal } from "../../lib/api/UserApi";
import { useRoute, useRouter } from "vue-router";
import { alertError, alertSuccess } from "../../lib/alert";
import { useLocalStorage } from "@vueuse/core";
import Swal from "sweetalert2";
import { useCardTheme } from "../../lib/useCardTheme";
import { store } from "../../lib/store";
import { useI18n } from "vue-i18n"; // <--- 1. TAMBAHKAN IMPORT INI

const { t } = useI18n(); // <--- 2. INISIALISASI i18n

const route = useRoute();
const router = useRouter();
const isLoading = ref(false);
const token = useLocalStorage("token", "");
const user = reactive({
  email: "",
  password: "",
});

const showPassword = ref(false);
const { initTheme } = useCardTheme();

// --- 1. FUNGSI REUSABLE UNTUK MENANGANI USER BANNED ---
async function handleBannedUser(emailTarget, message) {
  // Ganti teks manual dengan t(...)
  const { isConfirmed, value: reason } = await Swal.fire({
    icon: "error",
    title: t("auth.alert.banned_title"), // "Akun Dinonaktifkan"
    text: message || t("auth.alert.banned_text"), // "Akun Anda telah dibekukan."
    footer: `<span class="text-sm">${t("auth.alert.banned_footer")}</span>`, // "Tulis minimal 10 karakter."
    input: "textarea",
    inputLabel: t("auth.alert.appeal_label"), // "Alasan Banding"
    inputPlaceholder: t("auth.alert.appeal_placeholder"), // "Jelaskan mengapa akun Anda harus dipulihkan..."
    inputAttributes: {
      "aria-label": "Tulis pesan banding disini",
    },
    showCancelButton: true,
    confirmButtonText: t("auth.alert.btn_appeal"), // "Kirim Banding"
    cancelButtonText: t("auth.alert.btn_close"), // "Tutup"
    confirmButtonColor: "#9a203e",
  });

  if (isConfirmed && reason) {
    try {
      const resAppeal = await sendAppeal(emailTarget, reason);
      const jsonAppeal = await resAppeal.json();

      if (resAppeal.ok) {
        await alertSuccess(t("auth.alert.appeal_success")); // "Permintaan banding terkirim ke Admin."
      } else {
        await alertError(jsonAppeal.message || t("auth.alert.appeal_failed")); // "Gagal mengirim banding."
      }
    } catch (err) {
      console.error(err);
      await alertError(t("auth.alert.network_error")); // "Gagal terhubung ke server untuk banding."
    }
  }
}

async function handleSubmit() {
  isLoading.value = true; // Tambahkan loading state
  try {
    const response = await userLogin(user);
    const responseBody = await response.json();

    if (response.ok) {
      token.value = responseBody.token;
      sessionStorage.removeItem("last_anim_name");
      store.setUser(responseBody.user);

      // Logic Redirect
      const serverRole = responseBody.user?.role;
      const finalRole = String(serverRole || "").trim();

      if (finalRole === "admin") {
        await router.push("/dashboard/admin");
      } else {
        await router.push("/dashboard/global");
      }
    } else {
      if (response.status === 403 && responseBody.status === "banned") {
        await handleBannedUser(user.email, responseBody.message);
      } else {
        const pesanError = responseBody.errors ? Object.values(responseBody.errors)[0][0] : responseBody.message;
        await alertError(pesanError);
      }
    }
  } catch (error) {
    console.error("Login Error:", error);
    await alertError(t("auth.alert.server_error")); // "Terjadi kesalahan jaringan atau server."
  } finally {
    isLoading.value = false;
  }
}

const loginWithGoogle = () => {
  window.location.href = `${import.meta.env.VITE_APP_PATH}/auth/google/redirect`;
};

onMounted(async () => {
  const { error, status, email, message } = route.query;

  if (status === "banned" && email) {
    router.replace({ query: {} });
    handleBannedUser(email, message);
  } else if (error) {
    await alertError(error);
    router.replace({ query: {} });
  }
});
</script>

<template>
  <div
    class="min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 md:px-8 -mt-[2.8em] sm:-mt-[2.5em] md:-mt-[1em] mb-[4em] overflow-y-auto">
    <div
      class="bg-[#1c1516] text-[#e5e5e5] text-[14px] rounded-[30px] w-full max-w-[560px] px-4 py-8 shadow-2xl border border-[#2c2021]">
      <div class="flex flex-col items-center text-[#9a203e] mb-6">
        <h1 class="text-[28px] font-bold leading-tight">{{ $t("auth.login_title") }}</h1>
        <p class="mt-1 text-[13px] text-[#8c8a8a]">{{ $t("auth.login_subtitle") }}</p>
      </div>

      <form v-on:submit.prevent="handleSubmit" class="space-y-5">
        <div>
          <label class="block text-xs font-bold text-[#8c8a8a] uppercase tracking-wider mb-1">
            {{ $t("auth.label_email") }}
          </label>
          <input
            v-model="user.email"
            type="email"
            required
            :placeholder="$t('auth.placeholder_email')"
            class="w-full px-4 py-3 bg-[#2b2122] text-[#e5e5e5] rounded-[12px] focus:outline-none focus:ring-2 focus:ring-[#9a203e] placeholder-[#555] transition-all" />
        </div>

        <div>
          <div class="flex justify-between items-center mb-1">
            <label class="block text-xs font-bold text-[#8c8a8a] uppercase tracking-wider">
              {{ $t("auth.label_password") }}
            </label>
            <RouterLink
              to="/forgot-password"
              class="text-[11px] font-bold text-[#9a203e] hover:text-[#b82b4d] transition-colors">
              {{ $t("auth.forgot_password") }}
            </RouterLink>
          </div>
          <div class="relative">
            <input
              v-model="user.password"
              :type="showPassword ? 'text' : 'password'"
              required
              :placeholder="$t('auth.placeholder_password')"
              class="w-full px-4 py-3 bg-[#2b2122] text-[#e5e5e5] rounded-[12px] focus:outline-none focus:ring-2 focus:ring-[#9a203e] placeholder-[#555] pr-10 transition-all" />

            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute inset-y-0 right-0 px-3 flex items-center text-[#8c8a8a] hover:text-[#9a203e] transition-colors cursor-pointer"
              title="Lihat Password">
              <svg
                v-if="showPassword"
                xmlns="http://www.w3.org/2000/svg"
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
              <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round">
                <path
                  d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
              </svg>
            </button>
          </div>
        </div>

        <div class="pt-2 flex flex-col gap-4">
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full bg-[#9a203e] hover:bg-[#821c35] text-white font-bold py-3.5 rounded-[12px] transition-transform active:scale-95 shadow-lg shadow-[#9a203e]/20 cursor-pointer disabled:opacity-70 disabled:cursor-not-allowed">
            {{ isLoading ? $t("auth.btn_loading") : $t("auth.btn_login") }}
          </button>

          <div class="relative flex items-center">
            <div class="flex-grow border-t border-[#2c2021]"></div>
            <span class="flex-shrink mx-4 text-[#666] text-[10px] uppercase font-bold tracking-widest">
              {{ $t("auth.or") }}
            </span>
            <div class="flex-grow border-t border-[#2c2021]"></div>
          </div>

          <button
            type="button"
            @click="loginWithGoogle"
            class="w-full bg-white hover:bg-gray-100 text-[#1c1516] font-bold py-3.5 rounded-[12px] transition-transform active:scale-95 flex items-center justify-center gap-3 cursor-pointer">
            <svg class="w-5 h-5" viewBox="0 0 24 24">
              <path
                fill="#4285F4"
                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
              <path
                fill="#34A853"
                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
              <path
                fill="#FBBC05"
                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
              <path
                fill="#EA4335"
                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
            </svg>
            {{ $t("auth.btn_google_login") }}
          </button>
        </div>
      </form>

      <div class="text-center mt-6 pt-4 border-t border-[#2c2021]">
        <p class="text-[#8c8a8a] text-xs">
          {{ $t("auth.no_account") }}
          <RouterLink
            to="/register"
            class="text-[#9a203e] font-bold hover:text-[#b82b4d] transition-colors ml-1 cursor-pointer">
            {{ $t("auth.link_register") }}
          </RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>
