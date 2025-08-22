<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->is_superadmin) {
            return redirect('/')->with([
                'error' => 'Acceso no autorizado',
                'mensaje' => 'No tienes permisos de super administrador para acceder a esta sección.',
                'tipo' => 'alert-danger'
            ]);
        }

        return $next($request);
    }
}
