<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Enums\UserRole;

class TestUserManagement extends Command
{
    protected $signature = 'test:user-management';
    protected $description = 'Test the user management functionality';

    public function handle()
    {
        $this->info('Testing User Management Functionality...');

        // Show current users
        $users = User::all();
        $this->info("Current users in the system: {$users->count()}");

        foreach ($users as $user) {
            $this->info("- {$user->name} ({$user->email}) - Role: {$user->role->label()}");
        }

        // Test creating a test user
        $this->info('Creating a test user...');
        $testUser = User::create([
            'name' => 'Test User for Management',
            'email' => 'testuser@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Birthday,
            'email_verified_at' => now(),
        ]);

        $this->info("Created: {$testUser->name} ({$testUser->email}) - Role: {$testUser->role->label()}");

        // Show updated count
        $userCount = User::count();
        $this->info("Total users after creation: {$userCount}");

        // Clean up
        $this->info('Cleaning up test user...');
        $testUser->delete();

        $finalCount = User::count();
        $this->info("Final user count: {$finalCount}");

        $this->info('User management test completed!');
    }
}
