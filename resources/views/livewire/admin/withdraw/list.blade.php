<?php

use App\Enum\WithdrawStatus;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Models\Withdraw;
use App\Models\Transcation;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use App\Models\ChartOfAccount;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use App\Models\ChartOfAccountCategory;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Withdraw Request List')] class extends Component {
    use Toast;

    public string $search = '';
    public array $headers;
    public $withdraw = [];

    #[Rule('required')]
    public string $trx_id = '';

    public bool $confirmModal = false;

    public function mount()
    {
        $this->headers = $this->headers();
    }

    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'agent.user.name', 'label' => 'Agent'], ['key' => 'method.name', 'label' => 'Withdraw Method'], ['key' => 'amount', 'label' => 'Amount'], ['key' => 'description', 'label' => 'Description'], ['key' => 'trx_id', 'label' => 'Trx ID'], ['key' => 'status', 'label' => 'Status'], ['key' => 'approved_by', 'label' => 'Approved By'], ['key' => 'action_by', 'label' => 'Last Action By']];
    }
    public function withdraws()
    {
        return Withdraw::query()
            ->with(['agent.user', 'method', 'actionBy', 'approvedBy'])
            ->when($this->search, fn(Builder $q) => $q->whereAny(['trx_id', 'amount'], 'LIKE', "%$this->search%"))
            ->latest()
            ->paginate(10);
    }
    public function with(): array
    {
        return [
            'withdraws' => $this->withdraws(),
        ];
    }
    public function confirmWithdraw(Withdraw $withdraw)
    {
        $this->withdraw = $withdraw;
        $this->confirmModal = true;
    }
    public function submitWithdrawRequest()
    {
        $this->validate();
        try {
            $this->withdraw->update([
                'trx_id' => $this->trx_id,
                'approved_by' => auth()->user()->id,
                'status' => WithdrawStatus::Confirmed,
                'action_by' => auth()->user()->id,
            ]);
            $this->confirmModal = false;
            $this->success('Withdraw Request Confirmed Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div>
    <x-header title="Withdraw List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
        </x-slot:middle>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$withdraws" with-pagination>
            @scope('cell_id', $withdraw, $withdraws)
                {{ $loop->iteration + ($withdraws->currentPage() - 1) * $withdraws->perPage() }}
            @endscope
            @scope('cell_status', $withdraw)
                <x-badge value="{{ $withdraw->status->label() }}" class="bg-primary text-white p-3" />
            @endscope
            @scope('cell_approved_by', $withdraw)
                {{ $withdraw->approvedBy->name ?? 'N/A' }}
            @endscope
            @scope('cell_action_by', $withdraw)
                {{ $withdraw->actionBy->name ?? 'N/A' }}
            @endscope
            @scope('actions', $withdraw)
                <x-button label="Confirm" class="btn btn-primary btn-sm" wire:click="confirmWithdraw({{ $withdraw->id }})" />
            @endscope
        </x-table>
    </x-card>
    <x-modal wire:model="confirmModal" title="Confirm Withdraw Request" separator>
        <x-form wire:submit="submitWithdrawRequest">
            <x-input label="Transcation Id" wire:model="trx_id" class="mb-3" placeholder="Transcation Id" required />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.confirmModal = false" class="btn-sm" />
                <x-button type="submit" label="Confirm Request" class="btn-primary btn-sm"
                    spinner="submitWithdrawRequest" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
