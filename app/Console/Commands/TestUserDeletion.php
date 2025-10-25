<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Invitation;
use App\Enums\UserRole;

class TestUserDeletion extends Command
{
    protected $signature = 'test:user-deletion';
    protected $description = 'Test user deletion with foreign key constraints';

    public function handle()
    {
        $this->info('Testing User Deletion with Foreign Key Constraints...');

        // Create a test user
        $testUser = User::create([
            'name' => 'Test User with Invitations',
            'email' => 'test.user.invitations@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ]);

        $this->info("Created test user: {$testUser->name} (ID: {$testUser->id})");

        // Create invitations by this user
        for ($i = 1; $i <= 3; $i++) {
            $invitation = Invitation::create([
                'email' => "invitation{$i}@example.com",
                'name' => "Test Invitation {$i}",
                'role' => UserRole::Birthday,
                'token' => bin2hex(random_bytes(32)),
                'expires_at' => now()->addHours(48),
                'invited_by' => $testUser->id,
            ]);
            $this->info("Created invitation {$i}: {$invitation->email}");
        }

        // Check invitations count
        $invitationCount = Invitation::where('invited_by', $testUser->id)->count();
        $this->info("User has {$invitationCount} invitation(s)");

        // Test the deletion process (simulate what the component does)
        $this->info('Simulating deletion process...');

        try {
            // Delete invitations first
            $deletedInvitations = Invitation::where('invited_by', $testUser->id)->count();
            Invitation::where('invited_by', $testUser->id)->delete();
            $this->info("Deleted {$deletedInvitations} invitation(s)");

            // Delete user
            $testUser->delete();
            $this->info("Successfully deleted user: {$testUser->name}");

        } catch (\Exception $e) {
            $this->error("Deletion failed: " . $e->getMessage());

            // Clean up if deletion failed
            $testUser->delete();
            Invitation::where('email', 'like', 'invitation%@example.com')->delete();
        }

        $this->info('User deletion test completed!');
    }
}
