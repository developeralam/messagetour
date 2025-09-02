<?php

use Mary\Traits\Toast;
use App\Models\Service;
use App\Models\ContactUs;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Contact Messages')] class extends Component {
    use WithPagination, Toast;

    public string $search = '';
    public ContactUs $contact_us;
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];

    public function delete(ContactUs $contact_us)
    {
        try {
            $contact_us->update([
                'action_by' => auth()->user()->id,
            ]);
            $contact_us->delete();
            $this->success('Contact Message Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'first_name', 'label' => 'First Name'], ['key' => 'last_name', 'label' => 'Last Name'], ['key' => 'email_address', 'label' => 'Email'], ['key' => 'phone', 'label' => 'Mobile No'], ['key' => 'subject', 'label' => 'Subject'], ['key' => 'message', 'label' => 'Message'], ['key' => 'action_by', 'label' => 'Last Action By']];
    }
    public function contacts()
    {
        return ContactUs::query()->when($this->search, fn(Builder $q) => $q->whereAny(['first_name', 'last_name', 'email_address', 'phone', 'subject', 'message'], 'LIKE', "%$this->search%"))->orderBy(...array_values($this->sortBy))->latest()->paginate(20)->withQueryString();
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
            'headers' => $this->headers(),
            'contacts' => $this->contacts(),
        ];
    }
}; ?>

<div>
    <x-header title="Contact Message List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" class="custom-input max-w-36" placeholder="Search..." />
        </x-slot:middle>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$contacts" :sort-by="$sortBy" with-pagination>
            @scope('cell_id', $contact_us, $contacts)
                {{ $loop->iteration + ($contacts->currentPage() - 1) * $contacts->perPage() }}
            @endscope
            @scope('cell_subject', $contact_us)
                <p class="text-sm font-medium mt-2 hover:text-green-600 hover:underline">
                    {!! \Illuminate\Support\Str::limit(
                        $contact_us->subject ?? '',
                        30,
                        ' <span class="text-green-500 cursor-pointer">..</span>',
                    ) !!}
                </p>
            @endscope
            @scope('cell_message', $contact_us)
                <p class="text-sm font-medium mt-2 hover:text-green-600 hover:underline">
                    {!! \Illuminate\Support\Str::limit(
                        $contact_us->message ?? '',
                        70,
                        ' <span class="text-green-500 cursor-pointer">..</span>',
                    ) !!}
                </p>
            @endscope
            @scope('cell_action_by', $contact_us)
                {{ $contact_us->actionby->name ?? 'N/A' }}
            @endscope
            @scope('actions', $contact_us)
                <div class="flex items-center">
                    <x-button icon="o-trash" wire:click="delete({{ $contact_us['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" />
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
