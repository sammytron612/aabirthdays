<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Enums\UserRole;

class TestUserDisableEnable extends Command
{
    protected $signature = 'test:user-disable-enable';
    protected $description = 'Test user disable/enable functionality';

    public function handle()
    {
        $this->info('Testing User Disable/Enable Functionality...');

        // Create a test user
        $testUser = User::create([
            'name' => 'Test User for Disable/Enable',
            'email' => 'test.disable@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Birthday,
            'email_verified_at' => now(),
        ]);

        $this->info("Created test user: {$testUser->name} (Role: {$testUser->role->label()})");

        // Test disabling the user
        $this->info('Testing disable functionality...');
        $testUser->update(['role' => UserRole::Disabled]);
        $testUser->refresh();
        $this->info("User disabled. Current role: {$testUser->role->label()}");

        // Test enabling the user
        $this->info('Testing enable functionality...');
        $testUser->update(['role' => UserRole::Admin]);
        $testUser->refresh();
        $this->info("User enabled. Current role: {$testUser->role->label()}");

        // Test the toggle logic
        $this->info('Testing toggle logic...');

        // Simulate the component's toggle method
        if ($testUser->role === UserRole::Disabled) {
            $testUser->update(['role' => UserRole::Admin]);
            $this->info("Toggled from disabled to admin");
        } else {
            $testUser->update(['role' => UserRole::Disabled]);
            $this->info("Toggled from active to disabled");
        }

        $testUser->refresh();
        $this->info("Final role: {$testUser->role->label()}");

        // Clean up
        $testUser->delete();
        $this->info('Test user deleted. Test completed!');
    }
}
