<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Acceso restringido al panel de administración.'], 403);
            }

            return redirect()->route('admin.login')
                ->with('error', 'Acceso restringido al panel de administración.');
        }

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('admin.login')
                ->with('error', 'Tu cuenta está desactivada.');
        }

        return $next($request);
    }
}
