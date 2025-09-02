<?php

use App\Models\User;
use App\Enum\UserType;
use Mary\Traits\Toast;
use App\Enum\UserStatus;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('System User List')] class extends Component {
    use WithPagination, Toast;
    public array $headers;
    public string $search = '';
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public $createModal = false;
    public $editModal = false;
    public array $statuses;
    public Collection $roles;
    public User $user;

    #[Rule('required')]
    public $name;

    #[Rule('required')]
    public $email;

    #[Rule('required')]
    public $role_id;

    #[Rule('nullable')]
    public $password;

    #[Rule('nullable|same:password')]
    public $password_confirmation;

    #[Rule('required')]
    public $status;

    /**
     * Initialize component with headers.
     *
     * @return void
     */
    public function mount()
    {
        $this->headers = $this->headers();
        $this->statuses = UserStatus::getStatuses();
        $this->roles = Role::all(['id', 'name']);
        $this->status = UserStatus::Active; // default status
    }

    /**
     * Delete a system user.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function delete(User $user)
    {
        try {
            $user->delete();

            // Show success message
            $this->success('System User Deleted Successfully');
        } catch (\Throwable $th) {
            // Show error message (detailed in debug mode)
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    /**
     * Handles storing the new system user and assigning a role.
     *
     * @return void
     */
    public function storeUser()
    {
        // Validate inputs using attribute rules
        $this->validate([
            'password' => 'required|confirmed',
            'password_confirmation' => 'required|same:password',
        ]);

        try {
            // Create the new user
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'status' => $this->status,
                'type' => UserType::Admin,
            ]);

            // Assign selected role to user
            $role = Role::findOrFail($this->role_id);
            $user->assignRole($role);

            // Redirect with success message
            $this->success('System User Added Successfully');
            $this->createModal = false;
            $this->reset(['name', 'email', 'password', 'password_confirmation', 'role_id', 'status']);
        } catch (\Throwable $th) {
            $this->reset(['name', 'email', 'password', 'password_confirmation', 'role_id', 'status']);
            // Handle and display error
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    /**
     * Initialize the component with user data and supporting data.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function edit(User $user)
    {
        $this->user = $user;

        // Set form fields from user model
        $this->name = $user->name;
        $this->email = $user->email;
        $this->status = $user->status;
        $this->role_id = $user->roles->first()?->id;

        $this->editModal = true;
    }

    /**
     * Handle updating the user and syncing their role.
     *
     * @return void
     */
    public function updateUser()
    {
        // Validate fields
        $this->validate();

        try {
            // Update the user details
            $this->user->update([
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password ? Hash::make($this->password) : $this->user->password,
                'status' => $this->status,
            ]);

            // Sync the selected role
            $role = Role::findOrFail($this->role_id);
            $this->user->syncRoles([$role]);

            // Redirect with success
            $this->success('System User Updated Successfully');
            $this->editModal = false;
            $this->reset(['name', 'email', 'password', 'password_confirmation', 'role_id', 'status']);
        } catch (\Throwable $th) {
            $this->reset(['name', 'email', 'password', 'password_confirmation', 'role_id', 'status']);
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    /**
     * Define table headers for the view.
     *
     * @return array
     */
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'name', 'label' => 'Name'], ['key' => 'email', 'label' => 'Email'], ['key' => 'role', 'label' => 'Role'], ['key' => 'status', 'label' => 'Status']];
    }

    /**
     * Fetch a paginated list of admin users with roles.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function users()
    {
        return User::query()
            ->with('roles') // eager-load roles to avoid N+1 queries
            ->where('type', UserType::Admin) // only admin users
            ->when($this->search, fn(Builder $q) => $q->where('name', 'LIKE', '%' . $this->search . '%'))
            ->orderBy(...array_values($this->sortBy)) // dynamic sorting
            ->paginate(10);
    }

    /**
     * Data passed to the view.
     *
     * @return array
     */
    public function with(): array
    {
        return [
            'users' => $this->users(),
        ];
    }
}; ?>

<div>
    <x-header title="System User List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Add System User" icon="o-plus" @click="$wire.createModal = true"
                class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$users" :sort-by="$sortBy" with-pagination>
            @scope('cell_status', $user)
                @if ($user->status == \App\Enum\UserStatus::Active)
                    <x-badge value="{{ $user->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($user->status == \App\Enum\UserStatus::Inactive)
                    <x-badge value="{{ $user->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('cell_role', $user)
                <x-badge value="{{ $user->getRoleNames()->first() }}" class="bg-primary text-white p-3" />
            @endscope
            @scope('actions', $user)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $user['id'] }})" wire:confirm="Are you sure?"
                        class="btn-action btn-error" spinner="delete({{ $user['id'] }})" />
                    <x-button icon="s-pencil-square" wire:click="edit({{ $user['id'] }})"
                        spinner="edit({{ $user['id'] }})" class="btn-action btn-neutral" />
                </div>
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="createModal" title="Create System User" separator boxClass="max-w-3xl">
        <x-form wire:submit="storeUser">
            <div class="grid grid-cols-2 gap-4">
                <x-input label="Name" wire:model="name" placeholder="Name" required />
                <x-input label="Email" wire:model="email" placeholder="Email Address" required />
                <x-password label="Password" wire:model="password" placeholder="Password" required right />
                <x-password label="Confirm Password" wire:model="password_confirmation" placeholder="Confirm Password"
                    required right />
                <x-choices placeholder="Select one" label="Role" single placeholder="Select One" :options="$roles"
                    wire:model="role_id" required single />
                <x-choices placeholder="Select one" label="Status" single placeholder="Select One" :options="$statuses"
                    wire:model="status" required single />
            </div>
            <x-slot:actions>
                <x-button label="Cancel " @click="$wire.createModal = false" class="btn-sm" />
                <x-button type="submit" label="Add User" class="btn-primary btn-sm" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    <x-modal wire:model="editModal" title="Update User - {{ $user->name ?? '' }}" separator boxClass="max-w-3xl">
        <x-form wire:submit="updateUser">
            <div class="grid grid-cols-2 gap-4">
                <x-input label="Name" wire:model="name" placeholder="Name" required />
                <x-input label="Email" wire:model="email" placeholder="Email Address" required />
                <x-password label="Password" wire:model="password" placeholder="Password" right />
                <x-password label="Confirm Password" wire:model="password_confirmation" placeholder="Confirm Password"
                    right />
                <x-choices placeholder="Select one" label="Role" single placeholder="Select One" :options="$roles"
                    wire:model="role_id" required single />
                <x-choices placeholder="Select one" label="Status" single placeholder="Select One" :options="$statuses"
                    wire:model="status" required single />
            </div>
            <x-slot:actions>
                <x-button label="Cancel " @click="$wire.editModal = false" class="btn-sm" />
                <x-button type="submit" label="Save" class="btn-primary btn-sm" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
