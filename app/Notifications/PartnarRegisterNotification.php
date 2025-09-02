<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class PartnarRegisterNotification extends Notification
{
    use Queueable;

    protected $agent;

    /**
     * Create a new notification instance.
     */
    public function __construct($agent)
    {
        $this->agent = $agent;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database','broadcast'];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'partner_id' => $this->agent->id,
            'partner_name' => $this->agent->user->name,
            'message' => 'A new partner has registered',
        ]);
    }

    /**
     * Get the notification representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'partner_id' => $this->agent->id,
            'partner_name' => $this->agent->user->name,
            'message' => 'A new partner has registered',
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
