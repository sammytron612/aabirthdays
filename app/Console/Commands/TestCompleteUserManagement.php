<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Enums\UserRole;

class TestCompleteUserManagement extends Command
{
    protected $signature = 'test:complete-user-management';
    protected $description = 'Test complete user management functionality';

    public function handle()
    {
        $this->info('Testing Complete User Management Functionality...');

        // Create test users with different roles
        $adminUser = User::create([
            'name' => 'Test Admin User',
            'email' => 'test.admin@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ]);

        $birthdayUser = User::create([
            'name' => 'Test Birthday User',
            'email' => 'test.birthday@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Birthday,
            'email_verified_at' => now(),
        ]);

        $disabledUser = User::create([
            'name' => 'Test Disabled User',
            'email' => 'test.disabled@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Disabled,
            'email_verified_at' => now(),
        ]);

        $this->info("Created test users:");
        $this->info("- Admin: {$adminUser->name} (Role: {$adminUser->role->label()})");
        $this->info("- Birthday: {$birthdayUser->name} (Role: {$birthdayUser->role->label()})");
        $this->info("- Disabled: {$disabledUser->name} (Role: {$disabledUser->role->label()})");

        // Test disable functionality
        $this->info("\nTesting disable functionality:");
        $adminUser->update(['role' => UserRole::Disabled]);
        $adminUser->refresh();
        $this->info("Admin user disabled: {$adminUser->role->label()}");

        // Test enable functionality
        $this->info("\nTesting enable functionality:");
        $disabledUser->update(['role' => UserRole::Admin]);
        $disabledUser->refresh();
        $this->info("Disabled user enabled: {$disabledUser->role->label()}");

        // Show final state
        $this->info("\nFinal user states:");
        $allTestUsers = User::whereIn('email', [
            'test.admin@example.com',
            'test.birthday@example.com',
            'test.disabled@example.com'
        ])->get();

        foreach ($allTestUsers as $user) {
            $status = $user->role === UserRole::Disabled ? '❌ DISABLED' : '✅ ACTIVE';
            $this->info("- {$user->name}: {$user->role->label()} {$status}");
        }

        // Test role counts
        $adminCount = User::where('role', UserRole::Admin)->count();
        $birthdayCount = User::where('role', UserRole::Birthday)->count();
        $disabledCount = User::where('role', UserRole::Disabled)->count();

        $this->info("\nRole statistics:");
        $this->info("- Admin users: {$adminCount}");
        $this->info("- Birthday users: {$birthdayCount}");
        $this->info("- Disabled users: {$disabledCount}");

        // Clean up
        $this->info("\nCleaning up test users...");
        User::whereIn('email', [
            'test.admin@example.com',
            'test.birthday@example.com',
            'test.disabled@example.com'
        ])->delete();

        $this->info('Complete user management test finished!');
    }
}
