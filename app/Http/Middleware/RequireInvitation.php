<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireInvitation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Debug logging
        \Log::info('RequireInvitation middleware called for: ' . $request->path());
        \Log::info('Session data: ', session()->all());

        // Only apply invitation requirement to registration routes
        if ($request->routeIs('register') || $request->routeIs('register.store')) {
            // Allow GET requests with valid invitation token in session
            if ($request->isMethod('GET')) {
                if (!session()->has('invitation_token')) {
                    \Log::error('No invitation token in session for GET request');
                    abort(403, 'Registration is by invitation only.');
                }
            }

            // For POST requests (actual registration), verify invitation token
            if ($request->isMethod('POST')) {
                $token = session('invitation_token');
                if (!$token) {
                    \Log::error('No invitation token in session for POST request');
                    abort(403, 'Registration is by invitation only.');
                }

                // Verify invitation exists and is valid
                $invitation = \App\Models\Invitation::where('token', $token)->first();
                if (!$invitation || !$invitation->isValid()) {
                    \Log::error('Invalid or expired invitation token: ' . $token);
                    session()->forget(['invitation_token', 'invitation_email', 'invitation_name', 'invitation_role']);
                    abort(403, 'Invalid or expired invitation.');
                }
            }
        }

        return $next($request);
    }
}
