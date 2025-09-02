<?php

use Carbon\Carbon;
use Mary\Traits\Toast;
use App\Models\Coupon;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Coupon Code')] class extends Component {
    use WithPagination, Toast;

    public string $search = '';
    public $headers = [];
    public Coupon $coupon;

    #[Rule('required')]
    public $code;

    #[Rule('required')]
    public $amount;

    #[Rule('required')]
    public $expiry_date;

    #[Rule('nullable')]
    public $max_uses;

    public bool $createModal = false;
    public bool $editModal = false;

    public function mount()
    {
        $this->headers = $this->headers();
    }
    public function storePromocode()
    {
        $this->validate();
        try {
            Coupon::create([
                'code' => $this->code,
                'amount' => $this->amount,
                'expiry_date' => $this->expiry_date,
                'max_uses' => $this->max_uses,
            ]);
            $this->reset(['code', 'amount', 'expiry_date', 'max_uses']);
            $this->success('Coupon Code added successfully');
            $this->createModal = false;
        } catch (\Throwable $th) {
            $this->createModal = false;
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function edit(Coupon $coupon)
    {
        $this->coupon = $coupon;
        $this->code = $coupon->code;
        $this->amount = $coupon->amount;
        $this->expiry_date = Carbon::parse($coupon->expiry_date)->format('Y-m-d');
        $this->max_uses = $coupon->max_uses;
        $this->editModal = true;
    }
    public function updatePromocode()
    {
        $this->validate();
        try {
            $this->coupon->update([
                'code' => $this->code,
                'amount' => $this->amount,
                'expiry_date' => $this->expiry_date,
                'max_uses' => $this->max_uses,
                'action_by' => auth()->user()->id,
            ]);
            $this->reset(['code', 'amount', 'expiry_date', 'max_uses']);
            $this->success('Coupon Code Updated Successfully');
            $this->editModal = false;
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function delete(Coupon $coupon)
    {
        try {
            $coupon->update([
                'action_by' => auth()->user()->id,
            ]);
            $coupon->delete();
            $this->success('Coupon Code Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'code', 'label' => 'Coupon Code'], ['key' => 'amount', 'label' => 'Amount'], ['key' => 'expiry_date', 'label' => 'Expiry Date'], ['key' => 'max_uses', 'label' => 'Max Use'], ['key' => 'action_by', 'label' => 'Last Action By']];
    }
    public function promocodes()
    {
        return Coupon::query()->with('actionBy')->when($this->search, fn(Builder $q) => $q->whereAny(['code', 'amount', 'expiry_date', 'max_uses'], 'LIKE', "%$this->search%"))->latest()->paginate(20);
    }
    public function updated($property)
    {
        if (!is_array($property) && $property != '') {
            $this->resetPage();
        }
    }
    public function with(): array
    {
        return [
            'promocodes' => $this->promocodes(),
        ];
    }
}; ?>

<div>
    <x-header title="Coupon Code" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <div class="flex gap-2">
                <x-input icon="o-bolt" wire:submit.live="search" class="max-w-36" placeholder="Search..." />
            </div>
        </x-slot:middle>
        <x-slot:actions>
            <x-button icon="o-plus" label="Add Coupon" @click="$wire.createModal = true" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$promocodes" with-pagination>
            @scope('cell_id', $coupon, $promocodes)
                {{ $loop->iteration + ($promocodes->currentPage() - 1) * $promocodes->perPage() }}
            @endscope
            @scope('cell_expiry_date', $coupon)
                {{ Carbon::parse($coupon['expiry_date'])->format('Y, F j') }}
            @endscope
            @scope('cell_action_by', $coupon)
                {{ $coupon->actionBy->name ?? '' }}
            @endscope
            @scope('actions', $coupon)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $coupon['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" />
                    <x-button icon="s-pencil-square" wire:click="edit({{ $coupon['id'] }})"
                        class="btn-neutral btn-action" />
                </div>
            @endscope
        </x-table>
    </x-card>
    <x-modal wire:model="createModal" title="Add New Coupon" separator>
        <x-form wire:submit="storePromocode">
            <x-input label="Coupon Code" wire:model="code" placeholder="Coupon Code" required />
            <x-input label="Amount" type="number" wire:model="amount" placeholder="Amount" required />
            <x-datetime label="Expiry Date" wire:model="expiry_date" required />
            <x-input label="Max Use" type="number" wire:model="max_uses" placeholder="Max Use" />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.createModal = false" class="btn-sm" />
                <x-button type="submit" label="Add Coupon" class="btn-primary btn-sm" spinner="storePromocode" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    <x-modal wire:model="editModal" title="Update Coupon Code - {{ $coupon->code ?? '' }}" separator>
        <x-form wire:submit="updatePromocode">
            <x-input label="Coupon Code" wire:model="code" placeholder="Coupon Code" required />
            <x-input label="Amount" type="number" wire:model="amount" placeholder="Amount" required />
            <x-datetime label="Expiry Date" wire:model="expiry_date" required />
            <x-input label="Max Use" type="number" wire:model="max_uses" placeholder="Max Use" />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.editModal = false" class="btn-sm" />
                <x-button type="submit" label="Update Coupon" class="btn-primary btn-sm" spinner="updatePromocode" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
