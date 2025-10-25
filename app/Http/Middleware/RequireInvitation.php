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
        // Check if this is a registration request
        if ($request->routeIs('register')) {
            // Allow GET requests with valid invitation token in session
            if ($request->isMethod('GET')) {
                if (!session()->has('invitation_token')) {
                    abort(403, 'Registration is by invitation only.');
                }
            }

            // For POST requests (actual registration), verify invitation token
            if ($request->isMethod('POST')) {
                $token = session('invitation_token');
                if (!$token) {
                    abort(403, 'Registration is by invitation only.');
                }

                // Verify invitation exists and is valid
                $invitation = \App\Models\Invitation::where('token', $token)->first();
                if (!$invitation || !$invitation->isValid()) {
                    session()->forget(['invitation_token', 'invitation_email', 'invitation_name', 'invitation_role']);
                    abort(403, 'Invalid or expired invitation.');
                }
            }
        }

        return $next($request);
    }
}
