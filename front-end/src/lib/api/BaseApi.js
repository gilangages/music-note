// BaseApi.js
export async function customFetch(url, options = {}) {
  // --- 1. AMBIL BAHASA DARI LOCAL STORAGE ---
  const locale = localStorage.getItem("user-locale") || "id";

  // --- 2. SIAPKAN HEADER ---
  // Kita gabungkan header bawaan dengan header baru
  const headers = {
    Accept: "application/json",
    "Content-Type": "application/json",
    "Accept-Language": locale, // <--- INI KUNCINYA (Kirim info bahasa ke Backend)
    ...options.headers, // Tetap pertahankan header lain jika ada yg dikirim manual
  };

  // Opsional: Jika token belum di-handle di tempat lain, bisa pasang disini juga
  // const token = localStorage.getItem("token"); // atau replace logic token anda
  // if (token) {
  //    headers["Authorization"] = `Bearer ${token.replace(/"/g, "")}`;
  // }

  // --- 3. BUAT OPTIONS BARU ---
  const newOptions = {
    ...options,
    headers,
  };

  // --- 4. LAKUKAN FETCH DENGAN OPTION BARU ---
  const response = await fetch(url, newOptions);

  // --- LOGIC LAMA (TIDAK DIUBAH) ---
  // Cek apakah ini request ke login?
  const isLoginRequest = url.includes("/users/login");

  // Jika 401 DAN BUKAN request login, baru lakukan logout paksa
  if (response.status === 401 && !isLoginRequest) {
    localStorage.removeItem("token");
    sessionStorage.clear();

    window.location.href = "/login";
    return Promise.reject("Session expired");
  }

  return response;
}
