<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\BirthdayEmail;

class TestEmailQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email-queue {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email queueing functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        $this->info('Queueing test email to: ' . $email);

        try {
            Mail::to($email)->queue(new BirthdayEmail('Test Queue Email', 'This is a test message from the queue system.'));
            $this->info('Email queued successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to queue email: ' . $e->getMessage());
        }
    }
}
