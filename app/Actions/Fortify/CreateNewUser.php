<?php

namespace App\Actions\Fortify;

use App\Models\Invitation;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ];

        // If invitation token is provided, validate it
        if (isset($input['invitation_token'])) {
            $rules['invitation_token'] = ['required', 'string'];
        }

        Validator::make($input, $rules)->validate();

        $role = UserRole::Admin; // Default role

        // If registration is through invitation, validate and get role from invitation
        if (isset($input['invitation_token'])) {
            $invitation = Invitation::where('token', $input['invitation_token'])->first();

            if (!$invitation || !$invitation->isValid()) {
                throw new \Exception('Invalid or expired invitation.');
            }

            // Ensure email matches invitation
            if ($invitation->email !== $input['email']) {
                throw new \Exception('Email does not match invitation.');
            }

            $role = $invitation->role;

            // Mark invitation as accepted
            $invitation->markAsAccepted();
        }

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'role' => $role,
            'email_verified_at' => now(), // Auto-verify email for invited users
        ]);
    }
}
