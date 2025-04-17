<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $message;
    protected $sender;

    /**
     * Create a new notification instance.
     *
     * @param  string  $message
     * @param  string  $sender
     * @return void
     */
    public function __construct(string $message, string $sender)
    {
        $this->message = $message;
        $this->sender = $sender;
    }

    /**
     * The channels the notification should be sent on.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['database']; // We'll store it in the database for the notification page
        // You can also add 'mail' to send an email notification
        // Or 'broadcast' for real-time notifications (requires more setup)
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'message' => $this->message,
            'sender' => $this->sender,
            'created_at_formatted' => now()->diffForHumans(), // Example of adding formatted data
            // Add any other relevant data for this notification type
        ];
    }

    // Optional: If you want to send email notifications
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('You have a new message from ' . $this->sender)
                    ->line('Message: ' . $this->message)
                    ->action('View Message', url('/messages')); // Replace with your message URL
    }

    // Optional: If you want to broadcast real-time notifications
    // public function toBroadcast(mixed $notifiable): BroadcastMessage
    // {
    //     return new BroadcastMessage([
    //         'message' => $this->message,
    //         'sender' => $this->sender,
    //     ]);
    // }
}