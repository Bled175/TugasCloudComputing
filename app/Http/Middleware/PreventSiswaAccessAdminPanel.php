<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventSiswaAccessAdminPanel
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Prevent siswa role from accessing admin panel
        if (auth()->check() && auth()->user()->role === 'siswa') {
            auth()->logout();
            abort(403, 'Akses ditolak. Siswa tidak dapat mengakses panel admin.');
        }

        return $next($request);
    }
}
