<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    public function handle(Request $request, Closure $next, string $moduleCode): Response
    {
        if (! module_enabled($moduleCode)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Module '{$moduleCode}' is not licensed or disabled for this installation.",
                ], 403);
            }

            return response()->view('errors.module-disabled', ['module' => $moduleCode], 403);
        }

        return $next($request);
    }
}
