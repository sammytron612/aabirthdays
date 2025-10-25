<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Invitation;
use App\Enums\UserRole;
use App\Actions\Fortify\CreateNewUser;

class TestRegistrationFlow extends Command
{
    protected $signature = 'test:registration-flow';
    protected $description = 'Test the complete registration flow with birthday secretary replacement';

    public function handle()
    {
        $this->info('Testing Complete Registration Flow...');

        // Clean up any existing test data first
        User::where('email', 'like', '%@example.com')->delete();
        Invitation::where('email', 'like', '%@example.com')->delete();

        // Step 1: Create existing birthday secretaries
        $this->info('Step 1: Creating existing birthday secretaries...');
        $existingSecretary1 = User::create([
            'name' => 'Existing Secretary 1',
            'email' => 'secretary1@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Birthday,
            'email_verified_at' => now(),
        ]);

        $existingSecretary2 = User::create([
            'name' => 'Existing Secretary 2',
            'email' => 'secretary2@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Birthday,
            'email_verified_at' => now(),
        ]);

        $this->info("Created: {$existingSecretary1->name} ({$existingSecretary1->email}) - Role: {$existingSecretary1->role->label()}");
        $this->info("Created: {$existingSecretary2->name} ({$existingSecretary2->email}) - Role: {$existingSecretary2->role->label()}");

        // Step 2: Show current birthday secretaries
        $birthdayCount = User::where('role', UserRole::Birthday)->count();
        $this->info("Current Birthday Secretaries: {$birthdayCount}");

        // Step 3: Create invitation for new birthday secretary
        $this->info('Step 2: Creating invitation for new birthday secretary...');
        $adminUser = User::where('role', UserRole::Admin)->first();
        $invitation = Invitation::create([
            'email' => 'new.secretary@example.com',
            'name' => 'New Birthday Secretary',
            'role' => UserRole::Birthday,
            'token' => bin2hex(random_bytes(32)),
            'expires_at' => now()->addHours(48),
            'invited_by' => $adminUser->id,
        ]);
        $this->info("Created invitation for: {$invitation->email} - Role: {$invitation->role->label()}");

        // Step 4: Simulate user registration with CreateNewUser action
        $this->info('Step 3: Simulating user registration...');
        $createUserAction = new CreateNewUser();

        $newUser = $createUserAction->create([
            'name' => 'New Birthday Secretary',
            'email' => 'new.secretary@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'invitation_token' => $invitation->token,
        ]);

        $this->info("New user registered: {$newUser->name} ({$newUser->email}) - Role: {$newUser->role->label()}");

        // Step 5: Check status of existing secretaries
        $existingSecretary1->refresh();
        $existingSecretary2->refresh();
        $this->info("Existing secretary 1 status: {$existingSecretary1->name} - Role: {$existingSecretary1->role->label()}");
        $this->info("Existing secretary 2 status: {$existingSecretary2->name} - Role: {$existingSecretary2->role->label()}");

        // Step 6: Show final counts
        $birthdayCount = User::where('role', UserRole::Birthday)->count();
        $disabledCount = User::where('role', UserRole::Disabled)->count();
        $this->info("Final counts - Birthday Secretaries: {$birthdayCount}, Disabled: {$disabledCount}");

        // Clean up
        $this->info('Cleaning up test data...');
        $existingSecretary1->delete();
        $existingSecretary2->delete();
        $newUser->delete();
        $invitation->delete();
        $this->info('Test completed!');
    }
}
