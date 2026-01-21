<?php

use App\Models\User;
use App\Enum\UserType;
use App\Enum\GroupFlightType;
use Mary\Traits\Toast;
use App\Models\GroupFlight;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\GroupFlightBooking;
use App\Notifications\GroupFlightBookingNotification;
use Illuminate\Support\Facades\Auth;
use App\Traits\InteractsWithImageUploads;
use Illuminate\Support\Facades\Notification;

new #[Layout('components.layouts.app')] #[Title('Group Ticket')] class extends Component {
    use WithPagination, Toast, WithFileUploads, InteractsWithImageUploads;
    public array $headers;
    public $type = GroupFlightType::Regular;

    #[Rule('nullable')]
    public $visa;

    #[Rule('required')]
    public $passport;

    public function mount()
    {
        if (!Auth::check()) {
            return redirect('/customer/login');
        }
        $this->headers = $this->headers();
    }
    public function headers(): array
    {
        return [['key' => 'journey_route', 'label' => 'Route'], ['key' => 'return_route', 'label' => 'Return Route'], ['key' => 'airline_name', 'label' => 'Airline'], ['key' => 'journey_date', 'label' => 'Date'], ['key' => 'return_date', 'label' => 'Return Date'], ['key' => 'visa', 'label' => 'Visa'], ['key' => 'passport', 'label' => 'Passport']];
    }
    public function flights()
    {
        return GroupFlight::query()->where('type', $this->type)->latest()->paginate(20);
    }
    public function apply(GroupFlight $groupFlight)
    {
        $this->validate();
        try {
            $storedVisaPath = null;
            $storedPassportPath = null;
            if ($this->visa) {
                $storedVisaPath = $this->optimizeAndStoreImage(
                    $this->visa, // The file from Livewire
                    'public', // The disk to store on
                    'groupflightbooking', // The subdirectory within the disk
                    null, // Optional max width
                    null, // Optional max height
                    75, // WEBP quality
                );
            }
            if ($this->passport) {
                $storedPassportPath = $this->optimizeAndStoreImage(
                    $this->passport, // The file from Livewire
                    'public', // The disk to store on
                    'groupflightbooking', // The subdirectory within the disk
                    null, // Optional max width
                    null, // Optional max height
                    75, // WEBP quality
                );
            }
            $booking = GroupFlightBooking::create([
                'user_id' => auth()->user()->id,
                'group_flight_id' => $groupFlight->id,
                'visa' => $storedVisaPath ?? null,
                'passport' => $storedPassportPath,
            ]);

            // Send notifications
            $admin = User::where('type', UserType::Admin)->get();
            $message = $groupFlight->title . ' group flight booked by ' . auth()->user()->name;

            // Notify admin
            Notification::send($admin, new GroupFlightBookingNotification($booking, $message));

            $this->success('Flight Ticket Booking Done');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function with(): array
    {
        return [
            'flights' => $this->flights(),
        ];
    }
}; ?>

<div>
    <div class="flex gap-2 justify-center mt-4 text-center">
        <x-button wire:click="$set('type', {{ \App\Enum\GroupFlightType::Regular }})"
            class="{{ $type === \App\Enum\GroupFlightType::Regular ? 'bg-[#22C55E] text-white btn-md' : 'btn-md' }}"
            label="Regular Fare" />

        <x-button wire:click="$set('type', {{ \App\Enum\GroupFlightType::Umrah }})"
            class="{{ $type === \App\Enum\GroupFlightType::Umrah ? 'bg-[#22C55E] text-white btn-md' : 'btn-md' }}"
            label="Umrah Fare" />
    </div>

    <x-card class="mt-4">
        <x-table :headers="$headers" :rows="$flights" with-pagination>
            @scope('cell_id', $flight, $flights)
                {{ $loop->iteration + ($flights->currentPage() - 1) * $flights->perPage() }}
            @endscope
            @scope('cell_journey_date', $flight)
                {{ $flight->journey_date->format('d M,Y') }}
            @endscope
            @scope('cell_return_date', $flight)
                {{ $flight->return_date->format('d M,Y') }}
            @endscope
            @scope('cell_visa', $flight)
                <x-file wire:model="visa" />
            @endscope
            @scope('cell_passport', $flight)
                <x-file wire:model="passport" />
            @endscope
            @scope('actions', $flight)
                <div class="flex items-center">
                    <x-button label="Apply" wire:click="apply({{ $flight['id'] }})" class="btn-primary btn-sm text-white"
                        spinner="apply({{ $flight['id'] }})" />
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
