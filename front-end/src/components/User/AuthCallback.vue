<script setup>
import { onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useLocalStorage } from "@vueuse/core";
import { useCardTheme } from "../../lib/useCardTheme";
import { userDetail } from "../../lib/api/UserApi";

// 👇 1. IMPORT 'store' (yang reactive), BUKAN 'userState'
import { store } from "../../lib/store";
import { alertError } from "../../lib/alert";
import { useI18n } from "vue-i18n"; // Import i18n

const { t } = useI18n(); // Inisialisasi
const router = useRouter();
const route = useRoute();
const tokenStorage = useLocalStorage("token", "");
const { initTheme } = useCardTheme();

// ❌ HAPUS BARIS INI (Ini penyebab error "not a function")
// const userStore = userState();

onMounted(async () => {
  const token = route.query.token;
  const userQuery = route.query.user;
  const error = route.query.error;

  if (token) {
    try {
      tokenStorage.value = token;
      let userData = null;

      // A. Cek Data dari URL (Prioritas Utama & Cepat)
      if (userQuery) {
        try {
          userData = JSON.parse(decodeURIComponent(userQuery));
          console.log("⚡ Fast Login: Menggunakan data dari URL");
        } catch (e) {
          console.error("Gagal parse URL user, fallback ke API...");
        }
      }

      // B. Fallback ke API jika URL kosong/gagal
      if (!userData) {
        console.log("🌐 Slow Login: Fetching data via API...");
        const response = await userDetail(token);
        const responseBody = await response.json();

        if (!response.ok) throw new Error(responseBody.message);
        userData = responseBody.data || responseBody.user || responseBody;
      }

      // Validasi Data
      if (!userData || !userData.id) {
        // Menggunakan t() untuk error message
        throw new Error(t("auth_callback.error_data"));
      }

      // 👇 2. GUNAKAN 'setUser' DARI STORE.JS
      // Ini otomatis update state reactive DAN simpan ke localStorage ('user')
      store.setUser(userData);

      // Opsional: Jika aplikasi kamu di tempat lain memakai 'userState' (ref),
      // kamu bisa update manual di sini (tapi sebaiknya konsisten pakai 'store.user' saja)
      // userState.value = userData;

      // Load Tema

      // Redirect Clean
      window.location.href = "/dashboard/global";
    } catch (err) {
      console.error("Callback Error:", err);
      // Menggunakan t() dengan parameter message
      await alertError(t("auth_callback.error_login_prefix", { message: err.message }));
      router.push("/login");
    }
  } else if (error) {
    // Menggunakan t() dengan parameter error
    await alertError(t("auth_callback.error_general_prefix", { error: error }));
    router.push("/login");
  } else {
    router.push("/login");
  }
});
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-[#1c1516] text-white">
    <div class="flex flex-col items-center gap-4">
      <div class="w-10 h-10 border-4 border-[#9a203e] border-t-transparent rounded-full animate-spin"></div>
      <p class="text-[#e5e5e5] text-sm animate-pulse">{{ $t("auth_callback.processing") }}</p>
    </div>
  </div>
</template>
