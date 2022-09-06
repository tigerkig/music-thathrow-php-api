<?php

namespace App\Notifications;

use App\Models\Beat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BeatProcessedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public $beat;

    public function __construct(Beat $beat)
    {
        $this->beat = $beat;
        $this->afterCommit();
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(sprintf('%s has been processed', $this->beat->name))
            ->greeting(sprintf("Hello %s", $this->beat->creator->name))
            ->line('Congratulations')
            ->line(sprintf('%s has been processed and is now available on the website.', $this->beat->name))
            ->action('Go to beat', sprintf("%s/beats/%d", config('fortify.home'), $this->beat->id))
            ->line('Thank you for using our application!');
    }

    public function toArray($notifiable): array
    {
        return [
            'beat_id' => $this->beat->id,
            'beat_name' => $this->beat->name,
            'type' => 'beat_processed',
        ];
    }
}
