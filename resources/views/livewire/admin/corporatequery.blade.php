<?php

use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\CorporateQuery;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Mail;
use App\Mail\CorporateQueryInvoiceMail;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Corporate Query List')] class extends Component {
    use WithPagination, Toast, WithFileUploads;

    public array $headers;
    public string $search = '';
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public bool $sendInvoiceModal = false;

    public $selectedQuery;
    public $invoice;

    /**
     * Livewire mount method.
     * Initializes table headers for the corporate query listing.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->headers = $this->headers();
    }

    /**
     * Deletes a specific corporate query entry.
     *
     * @param CorporateQuery $query
     * @return void
     */
    public function delete(CorporateQuery $query): void
    {
        try {
            $query->update([
                'action_by' => auth()->user()->id,
            ]);
            $query->delete();
            $this->success('Query Deleted Successfully');
        } catch (\Throwable $th) {
            // Show debug error message if exception occurs
            $this->error($th->getMessage());
        }
    }

    /**
     * Defines table column headers for corporate query listing.
     *
     * @return array
     */
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'name', 'label' => 'Name'], ['key' => 'email', 'label' => 'Email'], ['key' => 'phone', 'label' => 'Mobile No'], ['key' => 'destination.name', 'label' => 'Destination'], ['key' => 'formatted_travel_date', 'label' => 'Travelling Date'], ['key' => 'program', 'label' => 'Program'], ['key' => 'hotel_type', 'label' => 'Hotel Type'], ['key' => 'hotel_room_type', 'label' => 'Room Type'], ['key' => 'visa_service', 'label' => 'Visa Service'], ['key' => 'air_ticket', 'label' => 'Air Ticket'], ['key' => 'tour_guide', 'label' => 'Tour Guide'], ['key' => 'status', 'label' => 'Status'], ['key' => 'action_by', 'label' => 'Last Action By']];
    }

    /**
     * Fetches and filters paginated list of corporate queries.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function queries()
    {
        return CorporateQuery::query()
            ->with(['destination', 'actionBy']) // Eager-load destination country
            ->when($this->search, fn(Builder $q) => $q->whereAny(['name', 'email', 'phone', 'program'], 'LIKE', "%{$this->search}%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    /**
     * Updates the status of a specific query entry.
     *
     * @param int $id
     * @param string $newStatus
     * @return void
     */
    public function changeStatus(int $id, string $newStatus): void
    {
        $query = CorporateQuery::findOrFail($id); // Ensure query exists
        $query->status = $newStatus;
        $query->save();

        $this->success('Status Updated Successfully!');
    }

    public function openInvoiceModal($id)
    {
        $this->selectedQuery = CorporateQuery::find($id);
        $this->sendInvoiceModal = true;
    }

    public function sendInvoice()
    {
        try {
            $fileContent = base64_encode(file_get_contents($this->invoice->getRealPath()));
            $fileName = $this->invoice->getClientOriginalName();
            $mimeType = $this->invoice->getMimeType();

            Mail::to($this->selectedQuery->email)->send(new CorporateQueryInvoiceMail($fileContent, $fileName, $mimeType));

            $this->reset('invoice');
            $this->success('Invoice Sent Successfully');
            $this->sendInvoiceModal = false;
        } catch (\Throwable $th) {
            $this->reset('invoice');
            $this->sendInvoiceModal = false;
            $this->error(env('APP_DEBUG') ? $th->getMessage() : 'Something went wrong.');
        }
    }

    /**
     * Provides data to be used inside the Livewire view.
     *
     * @return array
     */
    public function with(): array
    {
        return [
            'queries' => $this->queries(),
        ];
    }
}; ?>

<div>
    <x-header title="Corporate Query List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
        </x-slot:middle>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$queries" :sort-by="$sortBy" with-pagination>
            @scope('cell_id', $query, $queries)
                {{ $loop->iteration + ($queries->currentPage() - 1) * $queries->perPage() }}
            @endscope
            @scope('cell_visa_service', $query)
                {{ $query->visa_service == 1 ? 'Yes' : 'No' }}
            @endscope
            @scope('cell_air_ticket', $query)
                {{ $query->air_ticket == 1 ? 'Yes' : 'No' }}
            @endscope
            @scope('cell_tour_guide', $query)
                {{ $query->tour_guide == 1 ? 'Yes' : 'No' }}
            @endscope
            @scope('cell_hotel_type', $query)
                {{ $query->hotel_type->label() }}
            @endscope
            @scope('cell_hotel_room_type', $query)
                {{ $query->hotel_room_type->label() }}
            @endscope
            @scope('cell_action_by', $query)
                {{ $query->actionBy->name ?? '' }}
            @endscope
            @scope('cell_status', $query)
                <select wire:change="changeStatus({{ $query->id }}, $event.target.value)" class="border px-2 py-1">
                    @foreach (\App\Enum\CorporateQueryStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ $query->status == $status ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            @endscope
            @scope('actions', $query)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $query['id'] }})" wire:confirm="Are you sure?"
                        class="btn-action btn-error" spinner="delete({{ $query['id'] }})" />
                    <x-button icon="fas.print" external link="/admin/corporate-query/{{ $query['id'] }}"
                        class="btn-action btn-primary text-white" />
                    <x-button icon="fas.envelope" class="btn-action btn-primary"
                        wire:click="openInvoiceModal({{ $query->id }})" />
                </div>
            @endscope
        </x-table>
    </x-card>
    <x-modal title="Send Invoice" wire:model="sendInvoiceModal" size="text-xl" separator>
        <x-card>
            <x-file wire:model="invoice" label="Upload Invoice" required class="max-w-full" />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.sendInvoiceModal = false" class="btn-sm" />
                <x-button type="button" label="Send Mail" wire:click="sendInvoice" class="btn-primary btn-sm"
                    spinner="sendInvoice" />
            </x-slot:actions>
        </x-card>
    </x-modal>
</div>
