<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Verifica se o usuário está autenticado e tem role admin
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request);
        }

        // Se não for admin, redireciona ou retorna erro 403
        if (Auth::check()) {
            abort(403, 'Acesso não autorizado. Área restrita para administradores.');
        }

        // Se não estiver autenticado, redireciona para o login
        return redirect()->route('login');
    }
}