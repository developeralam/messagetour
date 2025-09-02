<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class CorporateQueryNotification extends Notification
{
    use Queueable;

    public $query;

    /**
     * Create a new notification instance.
     */
    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'queyr_id' => $this->query->id,
            'person_compony_name' => $this->query->user->name,
            'message' => 'A new corporate query has been store by'. $this->query->user->name,
        ]);
    }

    /**
     * Get the notification representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'queyr_id' => $this->query->id,
            'person_compony_name' => $this->query->user->name,
            'message' => 'A new corporate query collect by '. $this->query->user->name,
        ];
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
