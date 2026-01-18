<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoBadWords implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Daftar kata terlarang (bisa dipindah ke config/database nanti)
        $badWords = ['bodoh', 'anying', 'cacat', 'kntl', 'bangke', 'toket', 'coli', 'ngewe', 'bokep', 'mastrubasi', 'sange', 'sanhge', 'ngentot', 'tolol', 'perek', 'jancok', 'pelacur', 'lonte', 'asu', 'bangsat', 'jingan', 'anjing', 'goblok', 'asu', 'kontol', 'tai', 'eek', 'bajingan', 'pentil', 'memek', 'pepek', 'itil']; // Contoh saja

        // Normalisasi input user ke huruf kecil semua
        $valueLower = strtolower($value);

        foreach ($badWords as $word) {
            // 1. Pecah kata kotor menjadi array huruf (contoh: 'anjing' -> ['a','n','j','i','n','g'])
            $chars = mb_str_split($word);

            // 2. Buat pola Regex untuk setiap huruf
            $patternParts = array_map(function ($char) {
                // Escape karakter (aman jika ada simbol) & tambahkan '+' (artinya huruf bisa 1x atau lebih)
                return preg_quote($char, '/') . '+';
            }, $chars);

            // 3. Gabungkan huruf dengan pemisah spasi fleksibel (\s*)
            // \s* artinya: boleh ada spasi/tab, boleh juga tidak ada sama sekali.
            // Hasil pola untuk 'anjing': /a+\s*n+\s*j+\s*i+\s*n+\s*g+/i
            $pattern = '/' . implode('\s*', $patternParts) . '/i';

            // 4. Cek apakah input user cocok dengan pola tersebut
            if (preg_match($pattern, $valueLower)) {
                $fail("Kalimat mengandung kata yang tidak pantas.");
                return;
            }
        }
    }
}
