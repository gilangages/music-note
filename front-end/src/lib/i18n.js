import { createI18n } from "vue-i18n";
import id from "../locales/id.json";
import en from "../locales/en.json";

// Cek local storage, jika tidak ada gunakan 'id'
const savedLocale = localStorage.getItem("user-locale") || "id";

const i18n = createI18n({
  legacy: false, // Wajib false untuk Vue 3 Composition API
  locale: savedLocale,
  fallbackLocale: "id",
  globalInjection: true, // Agar $t bisa dipakai langsung di template
  warnHtmlInMessage: "off",
  messages: {
    id,
    en,
  },
});

export default i18n;
