<?php

use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

new #[Layout('components.layouts.admin')] #[Title('Update Role')] class extends Component {
    use Toast;
    public $permissions;
    #[Rule('required')]
    public $name;

    public $selectedPermissions = [];
    public $role;

    /**
     * Initialize the component with the existing role data.
     *
     * @param int $role ID of the role to edit
     * @return void
     */
    public function mount($role)
    {
        // Load the role with its permissions
        $role = Role::with('permissions:name')->findOrFail($role);

        $this->role = $role;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();

        // Load all available permissions
        $this->permissions = Permission::all(['id', 'name']);
    }

    /**
     * Updates the role and synchronizes the permissions.
     *
     * @return void
     */
    public function updateRole()
    {
        // Validate input fields
        $this->validate();

        try {
            // Update the role name
            $this->role->update(['name' => $this->name]);

            // Sync the selected permissions
            $this->role->syncPermissions($this->selectedPermissions);

            // Reset input fields
            $this->resetInputFields();

            // Show success and redirect
            $this->success('Role Updated Successfully', redirectTo: '/admin/role/list');
        } catch (\Throwable $th) {
            // Show error message with details in debug mode
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    /**
     * Resets form input fields to their default state.
     *
     * @return void
     */
    public function resetInputFields()
    {
        $this->name = '';
        $this->selectedPermissions = [];
    }
}; ?>

<div>
    <x-header title="Update - {{ $role->name }}" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Role List" icon="o-plus" link="/admin/role/list" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-form wire:submit="updateRole">
        <x-card>
            <x-devider title="Role Information" />
            <x-input label="Role Name" wire:model="name" placeholder="Manager" />
            <x-devider title="Permissions" />
            @foreach ($permissions as $permission)
                <div class="mb-4">
                    <x-checkbox label="{{ $permission->name }}" wire:model="selectedPermissions"
                        value="{{ $permission->name }}" />
                </div>
            @endforeach

            <x-slot:actions>
                <x-button label="Role List" link="/admin/role/list" class="btn-sm" />
                <x-button type="submit" label="Update Role" class="btn-primary btn-sm" spinner="updateRole" />
            </x-slot:actions>
        </x-card>
    </x-form>
</div>
