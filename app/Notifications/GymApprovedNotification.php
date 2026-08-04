<?php

namespace App\Notifications;

use App\Models\Gym;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GymApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(public Gym $gym) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your gym has been approved on GymFinder')
            ->greeting("Congratulations, {$notifiable->name}!")
            ->line("Your gym \"{$this->gym->name}\" has been approved and is now live on GymFinder.")
            ->action('View Listing', config('app.frontend_url')."/gyms/{$this->gym->slug}")
            ->line('Thanks for being part of GymFinder!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'gym_id' => $this->gym->id,
            'gym_name' => $this->gym->name,
            'message' => "Your gym \"{$this->gym->name}\" has been approved.",
        ];
    }
}
