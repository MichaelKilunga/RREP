<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signs out an account that was suspended while its session was still open,
 * so "Suspend" in the Users panel takes effect immediately.
 */
class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->status && $user->status !== 'Active') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => __('app.account_suspended')], 403);
            }

            return redirect()->route('login')->withErrors(['email' => __('app.account_suspended')]);
        }

        return $next($request);
    }
}
