<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRegistrationIsOpen
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! is_registration_open()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => __('New user registrations are currently closed by administration.'),
                ], 403);
            }

            return redirect()
                ->route('login')
                ->withErrors(['registration_closed' => __('New user registrations are currently closed by administration.')])
                ->with('error', __('New user registrations are currently closed by administration.'));
        }

        return $next($request);
    }
}
