<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\BirthdayEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class TestBirthdayEmail extends Command
{
    protected $signature = 'test:birthday-email {email?}';
    protected $description = 'Test the birthday email functionality';

    public function handle()
    {
        $email = $this->argument('email') ?? 'test@example.com';

        $this->info('Testing Birthday Email functionality...');
        $this->info("Sending test email to: {$email}");

        try {
            $testSubject = 'Test Birthday Email';
            $testMessage = "Hello!\n\nThis is a test email from the Birthday Management System.\n\nThe email sending functionality is now working correctly!\n\nBest regards,\nThe Birthday Team";

            Mail::to($email)->send(new BirthdayEmail($testSubject, $testMessage));

            $this->info('✅ Test email sent successfully!');
            $this->info('Check your email inbox to confirm delivery.');

        } catch (\Exception $e) {
            $this->error('❌ Failed to send test email: ' . $e->getMessage());
            $this->info('Please check your email configuration in .env file:');
            $this->info('- MAIL_MAILER');
            $this->info('- MAIL_HOST');
            $this->info('- MAIL_PORT');
            $this->info('- MAIL_USERNAME');
            $this->info('- MAIL_PASSWORD');
        }
    }
}
