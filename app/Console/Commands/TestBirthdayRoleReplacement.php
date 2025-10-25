<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;

class TestBirthdayRoleReplacement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:birthday-replacement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the birthday secretary role replacement functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Birthday Secretary Role Replacement...');

        // Create some test users with birthday role
        $this->info('Creating test birthday secretary users...');

        $user1 = User::create([
            'name' => 'Birthday Secretary 1',
            'email' => 'birthday1@test.com',
            'password' => 'password123',
            'role' => UserRole::Birthday,
            'email_verified_at' => now(),
        ]);

        $user2 = User::create([
            'name' => 'Birthday Secretary 2',
            'email' => 'birthday2@test.com',
            'password' => 'password123',
            'role' => UserRole::Birthday,
            'email_verified_at' => now(),
        ]);

        $this->info("Created users:");
        $this->info("- {$user1->name} ({$user1->email}) - Role: {$user1->role->label()}");
        $this->info("- {$user2->name} ({$user2->email}) - Role: {$user2->role->label()}");

        // Count current birthday secretaries
        $birthdayCount = User::where('role', UserRole::Birthday)->count();
        $this->info("Current Birthday Secretaries: {$birthdayCount}");

        // Simulate inviting a new birthday secretary
        $this->info("\nSimulating invitation of new Birthday Secretary...");

        $existingBirthdayUsers = User::where('role', UserRole::Birthday)->get();
        foreach ($existingBirthdayUsers as $user) {
            $user->update(['role' => UserRole::Disabled]);
            $this->info("- Disabled: {$user->name} ({$user->email})");
        }

        // Check results
        $birthdayCount = User::where('role', UserRole::Birthday)->count();
        $disabledCount = User::where('role', UserRole::Disabled)->count();

        $this->info("\nResults:");
        $this->info("- Birthday Secretaries: {$birthdayCount}");
        $this->info("- Disabled Users: {$disabledCount}");

        // Show all users by role
        $this->info("\nAll users by role:");
        foreach (User::all() as $user) {
            $this->info("- {$user->name} ({$user->email}) - Role: {$user->role->label()}");
        }

        // Clean up test users
        $this->info("\nCleaning up test users...");
        User::whereIn('email', ['birthday1@test.com', 'birthday2@test.com'])->delete();
        $this->info("Test users removed.");

        return 0;
    }
}
