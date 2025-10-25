<?php

namespace App\Console\Commands;

use App\Actions\Fortify\CreateNewUser;
use App\Enums\UserRole;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Console\Command;

class TestRegistration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:registration {email} {name} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the registration process with an invitation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->argument('name');
        $password = $this->argument('password');

        try {
            // First create an invitation
            $admin = User::where('role', UserRole::Admin)->first();
            if (!$admin) {
                $this->error('No admin user found. Please create an admin user first.');
                return 1;
            }

            $invitation = Invitation::createInvitation(
                $email,
                $name,
                UserRole::Admin,
                $admin->id
            );

            $this->info("Created invitation with token: {$invitation->token}");

            // Now test the registration process
            $createNewUser = new CreateNewUser();

            $userData = [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
                'invitation_token' => $invitation->token,
            ];

            $user = $createNewUser->create($userData);

            $this->info("User created successfully!");
            $this->info("User ID: {$user->id}");
            $this->info("User Name: {$user->name}");
            $this->info("User Email: {$user->email}");
            $this->info("User Role: {$user->role->label()}");
            $this->info("Email Verified: " . ($user->email_verified_at ? 'Yes' : 'No'));

            // Check if invitation was marked as accepted
            $invitation->refresh();
            $this->info("Invitation Accepted: " . ($invitation->isAccepted() ? 'Yes' : 'No'));

            return 0;

        } catch (\Exception $e) {
            $this->error("Registration failed: " . $e->getMessage());
            return 1;
        }
    }
}
