<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('logout', 'login.process')) {
            return $next($request);
        }

        $revoked = Cache::has('revoked-session:'.hash('sha256', $request->session()->getId()));

        if ($revoked || (Auth::check() && ! Auth::user()->is_active)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => User::INACTIVE_MESSAGE], 403);
            }

            return redirect()->route('login')->withErrors(['email' => User::INACTIVE_MESSAGE]);
        }

        return $next($request);
    }
}
