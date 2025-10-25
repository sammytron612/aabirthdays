<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\Invitation;
use App\Models\User;
use App\Notifications\InvitationNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class TestInvitation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:invitation {email} {name} {role=admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test invitation and display the signed URL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->argument('name');
        $role = $this->argument('role');

        // Validate role
        if (!in_array($role, ['admin', 'birthday'])) {
            $this->error('Role must be either "admin" or "birthday"');
            return 1;
        }

        // Get an admin user to act as inviter
        $admin = User::where('role', UserRole::Admin)->first();
        if (!$admin) {
            $this->error('No admin user found. Please create an admin user first.');
            return 1;
        }

        try {
            // Create invitation
            $invitation = Invitation::createInvitation(
                $email,
                $name,
                UserRole::from($role),
                $admin->id
            );

            $this->info("Invitation created successfully!");
            $this->info("Token: {$invitation->token}");
            $this->info("Expires at: {$invitation->expires_at}");

            // Generate the signed URL
            $signedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'invitation.accept',
                now()->addHours(48),
                ['token' => $invitation->token]
            );

            $this->info("Signed URL: {$signedUrl}");

            // Send notification (will be logged since MAIL_MAILER=log)
            Notification::route('mail', $email)->notify(new InvitationNotification($invitation));
            $this->info("Invitation email sent (check logs)");

            return 0;

        } catch (\Exception $e) {
            $this->error("Failed to create invitation: " . $e->getMessage());
            return 1;
        }
    }
}
