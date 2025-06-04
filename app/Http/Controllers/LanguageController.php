<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    /**
     * Altera o idioma do usuário logado
     *
     * @param Request $request
     * @param string $locale Idioma a ser definido (en ou pt-BR)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeLocale(Request $request, $locale)
    {
        // Verifica se o idioma solicitado é válido (apenas en e pt-BR são suportados)
        if (!in_array($locale, ['en', 'pt-BR'])) {
            $locale = 'en'; // Fallback para inglês se o idioma não for válido
        }

        // Se o usuário estiver logado, salva a preferência no perfil
        if (Auth::check()) {
            $user = Auth::user();
            $user->locale = $locale;
            $user->save();
        }
        
        // Define o idioma para a sessão atual
        app()->setLocale($locale);
        session()->put('locale', $locale);

        // Redireciona de volta para a página anterior com mensagem de sucesso
        return redirect()->back()->with('success', trans('messages.language_changed'));
    }
}
