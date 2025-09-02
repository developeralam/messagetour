<?php

use App\Models\Car;
use App\Enum\UserType;
use Mary\Traits\Toast;
use App\Enum\CarStatus;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] #[Title('Agent Vehicle List')] class extends Component {
    use WithPagination, Toast;

    public array $headers;
    public string $search = '';
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];

    public function mount(): void
    {
        $this->headers = $this->headers();
    }

    public function delete(Vehicle $car): void
    {
        try {
            $car->update([
                'action_id' => auth()->user()->id,
            ]);
            $car->delete();
            $this->success('Vehicle Deleted successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }

    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'image', 'label' => 'Vehicle'], ['key' => 'title', 'label' => 'Vehicle Name'], ['key' => 'car_type', 'label' => 'Vehicle Type'], ['key' => 'model_year', 'label' => 'Vehicle Model Year'], ['key' => 'country.name', 'label' => 'Country'], ['key' => 'division.name', 'label' => 'Division/State'], ['key' => 'district.name', 'label' => 'District/City'], ['key' => 'price_2_hours', 'label' => 'Price 2 Hour'], ['key' => 'price_4_hours', 'label' => 'Price 4 Hour'], ['key' => 'price_half_day', 'label' => 'Price Half Day'], ['key' => 'price_day', 'label' => 'Price Day'], ['key' => 'price_per_day', 'label' => 'Price Per Day'], ['key' => 'status', 'label' => 'Vehicle Status'], ['key' => 'action_id', 'label' => 'Last Action By']];
    }

    public function cars()
    {
        return Car::query()->with('actionby')->whereHas('createdby', fn($q) => $q->where('type', UserType::Agent))->when($this->search, fn($q) => $q->where('title', 'LIKE', "%$this->search%"))->orderBy(...array_values($this->sortBy))->paginate(10);
    }
    /**
     * Toggle the status of a car.
     * @param Car $car - The car whose status needs to be updated
     * @return void
     */
    public function changeStatus(Car $car): void
    {
        // Check the current status and set the new status accordingly
        if ($car->status == CarStatus::Available) {
            $newStatus = CarStatus::Unavailable;
        } elseif ($car->status == CarStatus::Unavailable) {
            $newStatus = CarStatus::Pending;
        } else {
            $newStatus = CarStatus::Available;
        }

        // Update the status and action_id for the car
        $car->update([
            'status' => $newStatus,
            'action_id' => auth()->user()->id,
        ]);

        $this->success('Vehicle Status Updated Successfully');
    }

    public function with(): array
    {
        return [
            'cars' => $this->cars(),
        ];
    }
}; ?>

<div>
    <x-header title="Agent Vehicle List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
        </x-slot:middle>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$cars" :sort-by="$sortBy" with-pagination>
            @scope('cell_id', $car, $cars)
                {{ $loop->iteration + ($cars->currentPage() - 1) * $cars->perPage() }}
            @endscope
            @scope('cell_car_type', $car)
                <x-badge value="{{ $car->car_type->label() }}" class="bg-primary text-white p-3 text-xs font-semibold" />
            @endscope
            @scope('cell_status', $car)
                <select wire:change.prevent="changeStatus({{ $car->id }})" class="border px-2 py-1">
                    <option {{ $car->status == \App\Enum\CarStatus::Available ? 'selected' : '' }}>
                        Available</option>
                    <option {{ $car->status == \App\Enum\CarStatus::Pending ? 'selected' : '' }}>
                        Pending</option>
                    <option {{ $car->status == \App\Enum\CarStatus::Unavailable ? 'selected' : '' }}>
                        Unavailable</option>
                </select>
            @endscope
            @scope('cell_image', $car)
                <x-avatar image="{{ $car->image_link ?? '/empty-user.jpg' }}" class="!w-10" />
            @endscope
            @scope('cell_action_id', $car)
                {{ $car->actionby->name ?? '' }}
            @endscope
            @scope('cell_price_2_hours', $car)
                {{ $car->price_2_hours > 0 ? 'BDT ' . number_format($car->price_2_hours) : '0' }}
            @endscope
            @scope('cell_price_4_hours', $car)
                {{ $car->price_4_hours > 0 ? 'BDT ' . number_format($car->price_4_hours) : '0' }}
            @endscope
            @scope('cell_price_half_day', $car)
                {{ $car->price_half_day > 0 ? 'BDT ' . number_format($car->price_half_day) : '0' }}
            @endscope
            @scope('cell_price_day', $car)
                {{ $car->price_day > 0 ? 'BDT ' . number_format($car->price_day) : '0' }}
            @endscope
            @scope('cell_price_per_day', $car)
                {{ $car->price_per_day > 0 ? 'BDT ' . number_format($car->price_per_day) : '0' }}
            @endscope
            @scope('actions', $car)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $car['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" spinner="delete({{ $car['id'] }})" />
                </div>
            @endscope
        </x-table>

    </x-card>
</div>
