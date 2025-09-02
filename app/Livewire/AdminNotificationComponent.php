<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class AdminNotificationComponent extends Component
{
    public $notifications;

    public $notificationsCount;

    public function mount()
    {
        $this->notifications = auth()->user()->unreadNotifications;
        $this->notificationsCount = $this->notifications->count();

    }

    #[On('echo:adminNotifications,.App\\Notifications\\OrderNotification')]
    public function refreshNotifications()
    {
        $this->notifications = auth()->user()->unreadNotifications;
        $this->notificationsCount = $this->notifications->count();
    }

    public function markAsRead($notificationId)
    {
        $notification = auth()->user()->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
        $this->refreshNotifications();
    }

    public function render()
    {
        return view('livewire.admin-notification-component');
    }
}
