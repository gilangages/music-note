import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";
import "dayjs/locale/id"; // Import bahasa Indonesia
import "dayjs/locale/en"; // TAMBAHAN: Import bahasa Inggris agar Day.js kenal
import i18n from "./i18n"; // TAMBAHAN: Import config i18n untuk cek bahasa aktif

// Aktifkan plugin relativeTime (untuk "yang lalu")
dayjs.extend(relativeTime);

// HAPUS atau KOMENTAR baris ini agar tidak memaksa global ID
// dayjs.locale("id");

export const formatTime = (dateString, relativeTo = null) => {
  if (!dateString) return "";

  // 1. Cek bahasa apa yang sedang dipakai user sekarang
  // Kita ambil value dari i18n global
  const currentLocale = i18n.global.locale.value;

  // 2. Pasang locale tersebut ke instance tanggal ini saja
  const date = dayjs(dateString).locale(currentLocale);

  // Gunakan waktu yang dipassing (reactive) atau waktu sekarang, sesuaikan juga localenya
  const now = relativeTo ? dayjs(relativeTo).locale(currentLocale) : dayjs().locale(currentLocale);

  // Logic ala WhatsApp Status (TETAP SAMA):
  // Jika bedanya lebih dari 24 jam, tampilkan tanggal lengkap
  // Jika kurang dari 24 jam, tampilkan "x jam yang lalu" atau "baru saja"
  if (now.diff(date, "day") >= 1) {
    return date.format("D MMM YYYY • HH:mm"); // Contoh: 12 Jan 2024 • 14:30
  }

  // Ini akan otomatis mengikuti bahasa (misal: "5 minutes ago" atau "5 menit yang lalu")
  return date.fromNow();
};

// Fungsi cek apakah sudah diedit (TETAP SAMA)
export const isEdited = (createdAt, updatedAt) => {
  if (!createdAt || !updatedAt) return false;

  // Ubah logic: Cek apakah updated_at lebih baru dari created_at (beda > 0 detik)
  // Kita pakai diff dalam satuan 'second' agar edit cepat pun terdeteksi
  return dayjs(updatedAt).diff(dayjs(createdAt), "second") > 0;
};
