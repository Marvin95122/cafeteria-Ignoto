<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        // Si el usuario no está logueado o su rol no está en la lista de permitidos, lo bloqueamos
        if (!auth()->check() || !in_array(auth()->user()->role, $roles)) {
            abort(403, 'No tienes permiso para acceder a esta pantalla.');
        }

        return $next($request);
    }
}
