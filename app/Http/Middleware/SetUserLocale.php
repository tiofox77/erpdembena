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
            app()->setLocale($locale);
        }
        
        return $next($request);
    }
}
