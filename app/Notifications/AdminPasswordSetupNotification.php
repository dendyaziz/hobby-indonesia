<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminPasswordSetupNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

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
        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'filament.admin.password.setup',
            now()->addDays(7),
            ['user' => $notifiable->id]
        );

        return (new MailMessage)
            ->subject('Setup Your Admin Password')
            ->line('You have been invited to join as an Admin.')
            ->line('Please click the button below to set your password. This link will expire in 7 days.')
            ->action('Set Password', $url)
            ->line('If you did not request an account, no further action is required.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
