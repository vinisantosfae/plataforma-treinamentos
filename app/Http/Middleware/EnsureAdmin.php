<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! ($request->user()?->currentAccessToken()?->pessoa?->is_admin ?? false)) {
            abort(403, 'Acesso restrito a administradores.');
        }

        return $next($request);
    }
}
