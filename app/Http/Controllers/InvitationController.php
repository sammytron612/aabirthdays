<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class InvitationController extends Controller
{
    public function accept(Request $request, string $token)
    {
        // Verify the signed URL
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid or expired invitation link.');
        }

        // Find the invitation
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation) {
            abort(404, 'Invitation not found.');
        }

        // Check if invitation is still valid
        if (!$invitation->isValid()) {
            if ($invitation->isExpired()) {
                abort(410, 'This invitation has expired.');
            }

            if ($invitation->isAccepted()) {
                abort(410, 'This invitation has already been used.');
            }
        }

        // Store invitation data in session for registration
        session([
            'invitation_token' => $token,
            'invitation_email' => $invitation->email,
            'invitation_name' => $invitation->name,
            'invitation_role' => $invitation->role->value,
        ]);

        // Redirect to registration page
        return redirect()->route('register');
    }
}
