<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BirthdayEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $emailSubject;
    public $emailMessage;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 120;

    /**
     * Create a new message instance.
     */
    public function __construct($subject, $message)
    {
        $this->emailSubject = $subject;
        $this->emailMessage = $message;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // Get the birthday secretary (user with Birthday role)
        $birthdaySecretary = \App\Models\User::where('role', \App\Enums\UserRole::Birthday)->first();

        $secretaryName = $birthdaySecretary ? $birthdaySecretary->name : 'Birthday Secretary';

        return $this->subject($this->emailSubject)
                    ->view('emails.birthday')
                    ->with([
                        'emailSubject' => $this->emailSubject,
                        'emailMessage' => $this->emailMessage,
                        'secretaryName' => $secretaryName,
                    ]);
    }    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
