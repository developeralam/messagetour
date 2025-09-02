<?php

use Mary\Traits\Toast;
use App\Models\Subscriber;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Jobs\SendSubscriberMailJob;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Subscribers')] class extends Component {
    use WithPagination, Toast;

    public Subscriber $subscriber;
    public string $search = '';
    public bool $emailModal = false;
    public array $selectedSubscribers;
    public $subject;
    public $email_body;

    public function delete(Subscriber $subscriber)
    {
        try {
            $subscriber->update([
                'action_id' => auth()->user()->id,
            ]);
            $subscriber->delete();
            $this->success('Subscriber Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'email', 'label' => 'Subscriber'], ['key' => 'action_by', 'label' => 'Last Action By']];
    }
    public function subscribers()
    {
        return Subscriber::query()->with('actionBy')->when($this->search, fn(Builder $q) => $q->where('email', 'LIKE', "%{$this->search}%"))->latest()->paginate(10);
    }
    public function sendMail()
    {
        try {
            $subscriber_emails = Subscriber::whereIn('id', $this->selectedSubscribers)->pluck('email')->toArray();
            if (!empty($subscriber_emails)) {
                $to_email = array_shift($subscriber_emails);
                $bcc_emails = $subscriber_emails;
                SendSubscriberMailJob::dispatch($to_email, $bcc_emails, $this->subject, $this->email_body);
                $this->subject = '';
                $this->email_body = '';
                $this->success('E-mail Sent Successfully');
                $this->emailModal = false;
            }
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
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
            'subscribers' => $this->subscribers(),
        ];
    }
}; ?>

<div>
    <x-header title="Subscriber List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." class="max-w-36" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Send Sms" @click="$wire.smsModal = true" class="btn-primary btn-sm" />
            <x-button label="Send Mail" @click="$wire.emailModal = true" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card class="mt-2">
        <x-table :headers="$headers" :rows="$subscribers" with-pagination selectable wire:model.live="selectedSubscribers">
            @scope('cell_id', $subscriber, $subscribers)
                {{ $loop->iteration + ($subscribers->currentPage() - 1) * $subscribers->perPage() }}
            @endscope
            @scope('cell_action_by', $subscriber)
                {{ $subscriber->actionBy->name ?? 'N/A' }}
            @endscope
            @scope('actions', $subscriber)
                <div class="flex items-center">
                    <x-button icon="o-trash" wire:click="delete({{ $subscriber['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" />
                </div>
            @endscope
        </x-table>
    </x-card>
    <x-modal wire:model="emailModal" title="Send Email To Subscriber" separator>
        <x-form wire:submit="sendMail">
            <x-input label="Subject" wire:model="subject" required />
            <x-textarea label="Mail Body" wire:model="email_body" required />
            <x-slot:actions>
                <x-button class="btn-sm" label="Cancel" @click="$wire.emailModal = false" />
                <x-button type="submit" class="btn-primary btn-sm" label="Send Email" spinner="sendMail" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
