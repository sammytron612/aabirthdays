<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Invitation;
use App\Enums\UserRole;

class TestInvitationFlow extends Command
{
    protected $signature = 'test:invitation-flow';
    protected $description = 'Test the complete invitation flow with birthday secretary replacement';

    public function handle()
    {
        $this->info('Testing Complete Invitation Flow...');

        // Clean up any existing test data first
        User::where('email', 'like', '%@example.com')->delete();
        Invitation::where('email', 'like', '%@example.com')->delete();

        // Step 1: Create a test birthday secretary
        $this->info('Step 1: Creating existing birthday secretary...');
        $existingSecretary = User::create([
            'name' => 'Existing Birthday Secretary',
            'email' => 'existing.secretary@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Birthday,
            'email_verified_at' => now(),
        ]);
        $this->info("Created: {$existingSecretary->name} ({$existingSecretary->email}) - Role: {$existingSecretary->role->label()}");

        // Step 2: Show current birthday secretaries
        $birthdayCount = User::where('role', UserRole::Birthday)->count();
        $this->info("Current Birthday Secretaries: {$birthdayCount}");

        // Step 3: Simulate the ManageInvites sendInvite logic for birthday role
        $this->info('Step 3: Simulating invitation for new birthday secretary...');
        $newRole = UserRole::Birthday;

        if ($newRole === UserRole::Birthday) {
            $this->info('Disabling existing birthday secretaries...');
            $existingBirthdayUsers = User::where('role', UserRole::Birthday)->get();
            foreach ($existingBirthdayUsers as $user) {
                $user->update(['role' => UserRole::Disabled]);
                $this->info("- Disabled: {$user->name} ({$user->email})");
            }
        }

        // Step 4: Create invitation for new birthday secretary
        $adminUser = User::where('role', UserRole::Admin)->first();
        $invitation = Invitation::create([
            'email' => 'new.secretary@example.com',
            'name' => 'New Birthday Secretary',
            'role' => $newRole,
            'token' => bin2hex(random_bytes(32)),
            'expires_at' => now()->addHours(48),
            'invited_by' => $adminUser->id,
        ]);
        $this->info("Created invitation for: {$invitation->email} - Role: {$invitation->role->label()}");

        // Step 5: Check if existing birthday secretary was disabled
        $existingSecretary->refresh();
        $this->info("Existing secretary status: {$existingSecretary->name} - Role: {$existingSecretary->role->label()}");

        // Step 6: Show final counts
        $birthdayCount = User::where('role', UserRole::Birthday)->count();
        $disabledCount = User::where('role', UserRole::Disabled)->count();
        $this->info("Final counts - Birthday Secretaries: {$birthdayCount}, Disabled: {$disabledCount}");

        // Clean up
        $this->info('Cleaning up test data...');
        $existingSecretary->delete();
        $invitation->delete();
        $this->info('Test completed!');
    }
}
