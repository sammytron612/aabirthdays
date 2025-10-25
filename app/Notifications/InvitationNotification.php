<?php

namespace App\Notifications;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class InvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Invitation $invitation
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $signedUrl = URL::temporarySignedRoute(
            'invitation.accept',
            now()->addHours(48),
            ['token' => $this->invitation->token]
        );

        return (new MailMessage)
            ->subject('You\'re invited to join ' . config('app.name'))
            ->greeting('Hello ' . $this->invitation->name . '!')
            ->line('You have been invited to join ' . config('app.name') . ' as a ' . $this->invitation->role->label() . '.')
            ->line('This invitation was sent by ' . $this->invitation->invitedBy->name . '.')
            ->action('Accept Invitation', $signedUrl)
            ->line('This invitation will expire in 48 hours.')
            ->line('If you did not expect to receive this invitation, please ignore this email.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'email' => $this->invitation->email,
            'role' => $this->invitation->role->value,
        ];
    }
}
            //
        ];
    }
}
