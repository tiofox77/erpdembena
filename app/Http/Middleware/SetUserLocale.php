<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUserLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se o usuário estiver autenticado, define o idioma com base na preferência do usuário
        if (auth()->check()) {
            $locale = auth()->user()->locale ?? 'en';
            
            // Sanitize locale to prevent UTF-8 issues
            $locale = preg_replace('/[^a-zA-Z_-]/', '', $locale);
            
            // Validate locale format
            if (in_array($locale, ['en', 'pt', 'pt_BR', 'pt-BR'])) {
                app()->setLocale($locale);
            } else {
                app()->setLocale('en'); // Default fallback
            }
        }
        
        return $next($request);
    }
}
