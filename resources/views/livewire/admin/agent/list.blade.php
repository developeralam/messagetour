<?php

use App\Models\User;
use App\Models\Agent;
use Mary\Traits\Toast;
use App\Mail\AgentMail;
use App\Enum\AgentStatus;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Jobs\SendAgentMailJob;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Agent List')] class extends Component {
    use WithPagination, Toast;
    public array $headers;
    public string $search = '';
    public bool $smsModal = false;
    public bool $emailModal = false;
    public array $selectedAgents;
    public $body;
    public $subject;
    public $email_body;

    public function mount()
    {
        $this->headers = $this->headers();
    }

    /**
     * Delete an agent and its associated user.
     *
     * This function ensures that both the agent and user are deleted within a transaction
     * to maintain data integrity. If an error occurs, the deletion is rolled back.
     *
     * @param User $user The user associated with the agent to be deleted.
     * @return void
     */
    public function delete(User $user)
    {
        // Start transaction to ensure both deletions occur safely
        DB::beginTransaction();

        try {
            $user->agent()->update([
                'action_by' => auth()->user()->id,
            ]);
            $user->agent()->delete();
            $user->delete();

            DB::commit();
            $this->success('Agent deleted successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function approve(Agent $agent)
    {
        try {
            $agent->update([
                'status' => AgentStatus::Approve,
                'action_by' => auth()->user()->id,
            ]);
            $this->success('Agent Approve Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'business_logo', 'label' => 'Business Logo'], ['key' => 'business_name', 'label' => 'Business Name'], ['key' => 'agent_type', 'label' => 'Business Type'], ['key' => 'validity', 'label' => 'Business Validtty'], ['key' => 'user.name', 'label' => 'Agent Name'], ['key' => 'user.email', 'label' => 'Agent Email'], ['key' => 'agent_status', 'label' => 'Agent Status'], ['key' => 'status', 'label' => 'Business Status'], ['key' => 'action_by', 'label' => 'Last Action By']];
    }

    /**
     * Retrieve a paginated list of agents with their associated users.
     *
     * This function allows searching by `business_name` and applies eager loading
     * to optimize query performance.
     *
     * @return \Livewire\WithPagination Paginated list of agents.
     */
    public function agents()
    {
        return Agent::query()
            ->with(['user']) // Eager load user relationship for efficiency
            ->when($this->search, fn(Builder $q) => $q->where('business_name', 'LIKE', "%{$this->search}%")) // Apply search filter if provided
            ->latest() // Order by latest entries
            ->paginate(10); // Paginate results
    }

    public function sendSms()
    {
        try {
            $agents = Agent::whereIn('id', $this->selectedAgents)->pluck('business_phone')->toArray();
            $phoneNumbers = implode(',', $agents);

            sms_send($phoneNumbers, $this->body);
            $this->body = '';

            $this->success('SMS Sent Successfully');
            $this->smsModal = false;
        } catch (\Throwable $th) {
            $this->smsModal = false;
            $this->error($th->getMessage());
        }
    }
    public function sendMail()
    {
        try {
            $agent_emails = Agent::whereIn('id', $this->selectedAgents)->pluck('business_email')->toArray();
            if (!empty($agent_emails)) {
                $to_email = array_shift($agent_emails);
                $bcc_emails = $agent_emails;
                SendAgentMailJob::dispatch($to_email, $bcc_emails, $this->subject, $this->email_body);
                $this->subject = '';
                $this->email_body = '';
                $this->success('Mail Sent Successfully');
                $this->emailModal = false;
            }
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
    public function login($customerId)
    {
        try {
            $user = User::find($customerId);
            Auth::login($user);
            request()->session()->regenerate();
            return $this->success('Login Successful', redirectTo: '/partner/dashboard');
        } catch (\Throwable $th) {
            return $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function with(): array
    {
        return [
            'agents' => $this->agents(),
        ];
    }
}; ?>

<div>
    <x-header title="Agent List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Send Sms" @click="$wire.smsModal = true" class="btn-primary btn-sm" />
            <x-button label="Send Mail" @click="$wire.emailModal = true" class="btn-primary btn-sm" />
            <x-button label="Add Agent" icon="o-plus" link="/admin/agent/create" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$agents" with-pagination selectable wire:model.live="selectedAgents">
            @scope('cell_id', $agent, $agents)
                {{ $loop->iteration + ($agents->currentPage() - 1) * $agents->perPage() }}
            @endscope
            @scope('cell_agent_type', $agent)
                <x-badge value="{{ $agent->agent_type->label() }}" class="bg-primary text-white p-3 text-xs" />
            @endscope
            @scope('cell_agent_status', $agent)
                @if ($agent->user->status == \App\Enum\UserStatus::Active)
                    <x-badge value="{{ $agent->user->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($agent->user->status == \App\Enum\UserStatus::Inactive)
                    <x-badge value="{{ $agent->user->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('cell_status', $agent)
                @if ($agent->status == \App\Enum\AgentStatus::Approve)
                    <x-badge value="{{ $agent->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($agent->status == \App\Enum\AgentStatus::Pending)
                    <x-badge value="{{ $agent->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('cell_validity', $agent)
                {{ optional($agent->validity)->format('d M, Y') ?? 'N/A' }}
            @endscope
            @scope('cell_business_logo', $agent)
                <x-avatar image="{{ $agent->business_logo_link ?? '/empty-user.jpg' }}" class="!w-10" />
            @endscope
            @scope('cell_business_name', $agent)
                {{ $agent->business_name ?? 'N/A' }}
            @endscope
            @scope('cell_action_by', $agent)
                {{ $agent->actionBy->name ?? 'N/A' }}
            @endscope
            @scope('actions', $agent)
                <div class="flex items-center gap-1">
                    @if ($agent->status == \App\Enum\AgentStatus::Pending)
                        <x-button icon="fas.check" wire:click="approve({{ $agent->id }})"
                            wire:confirm="Are you sure approve this agent?" class="btn-primary btn-action text-white"
                            spinner="approve({{ $agent['id'] }})" />
                    @endif
                    <x-button icon="o-trash" wire:click="delete({{ $agent->user->id }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" spinner="delete({{ $agent->user['id'] }})" />
                    <x-button icon="s-pencil-square" link="/admin/agent/{{ $agent['id'] }}/edit"
                        class="btn-neutral btn-action" />
                    <x-button icon="fas.right-to-bracket" wire:click="login({{ $agent->user->id }})"
                        class="btn-primary btn-action text-white" spinner="login({{ $agent->user->id }})" />
                    <x-button icon="o-document-text" link="/admin/agent/{{ $agent['id'] }}/ledger"
                        class="btn-info btn-action text-white" title="Ledger Report" />
                </div>
            @endscope
        </x-table>
    </x-card>
    <x-modal wire:model="smsModal" title="Send Sms to Agent" size="text-lg" separator>
        <x-form wire:submit="sendSms">
            <x-textarea wire:model="body" placeholder="Body" required />
            <x-slot:actions>
                <x-button class="btn-sm" label="Cancel" @click="$wire.smsModal = false" />
                <x-button type="submit" class="btn-primary btn-sm" label="Send SMS" />
            </x-slot>
        </x-form>
    </x-modal>
    <x-modal wire:model="emailModal" title="Send Email" separator>
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
