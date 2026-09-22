<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_admin) {
            Log::warning('Admin access denied.', [
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            abort(403);
        }

        return $next($request);
    }
}
