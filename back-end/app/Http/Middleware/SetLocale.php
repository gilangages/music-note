<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah ada header 'Accept-Language' dari Vue
        if ($request->hasHeader('Accept-Language')) {
            $locale = $request->header('Accept-Language');

            // Pastikan hanya bahasa yang didukung (id atau en)
            if (in_array($locale, ['id', 'en'])) {
                App::setLocale($locale);
            }
        }

        return $next($request);
    }
}
