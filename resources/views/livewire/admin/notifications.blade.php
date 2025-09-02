<?php

use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] #[Title('Notification List')] class extends Component {
    use Toast, WithPagination;

    public $headers = [];

    public function mount()
    {
        $this->headers = $this->headers();
    }
    public function headers(): array
    {
        return [['key' => 'type', 'label' => 'Notification Type'], ['key' => 'data', 'label' => 'Message'], ['key' => 'details', 'label' => 'Details'], ['key' => 'created_at', 'label' => 'Created At']];
    }

    public function delete($id)
    {
        auth()->user()->notifications()->findOrFail($id)->delete();
        $this->success('Notification Deleted Successfully.');
    }

    public function notifications()
    {
        return auth()->user()->notifications()->latest()->paginate(20);
    }
    public function with(): array
    {
        return [
            'notifications' => $this->notifications(),
        ];
    }
}; ?>

<div>
    <x-header title="Notification List" size="text-xl" separator class="bg-white px-2 pt-2" />
    <x-card>
        <x-table :headers="$headers" :rows="$notifications" with-pagination>
            @scope('cell_data', $notification)
                {{ $notification['data']['message'] ?? '-' }}
            @endscope
            @scope('cell_type', $notification)
                {{ class_basename($notification['type']) }}
            @endscope

            @scope('cell_details', $notification)
                @php
                    $type = class_basename($notification['type']);
                    $data = $notification['data'];
                @endphp

                @if ($type === 'OrderNotification')
                    <strong>Order ID:</strong> {{ $data['order_id'] ?? '-' }},
                    <strong>Customer:</strong> {{ $data['customer_name'] ?? '-' }}
                @elseif ($type === 'PartnarRegisterNotification')
                    <strong>Partner ID:</strong> {{ $data['partner_id'] ?? '-' }},
                    <strong>Partner:</strong> {{ $data['partner_name'] ?? '-' }}
                @elseif ($type === 'GroupFlightBookingNotification')
                    <strong>Booking ID:</strong> {{ $data['booking_id'] ?? '-' }},
                    <strong>Customer:</strong> {{ $data['customer_name'] ?? '-' }}
                @elseif ($type === 'CorporateQueryNotification')
                    <strong>Corporate Query ID:</strong> {{ $data['queyr_id'] ?? '-' }},
                    <strong>Customer/Company Name:</strong> {{ $data['person_compony_name'] ?? '-' }}
                @else
                    <span>-</span>
                @endif
            @endscope
            @scope('cell_created_at', $notification)
                {{ $notification->created_at->diffForHumans() }}
            @endscope
            @scope('actions', $notification)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete('{{ $notification->id }}')" wire:confirm="Are you sure?"
                        class="btn-error btn-action" spinner="delete('{{ $notification->id }}')" />

                </div>
            @endscope
        </x-table>

    </x-card>
</div>
