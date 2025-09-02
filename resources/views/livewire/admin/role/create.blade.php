<?php

use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

new #[Layout('components.layouts.admin')] #[Title('Add New Role')] class extends Component {
    use Toast;
    public $permissions;

    #[Rule('required')]
    public $name;

    public $selectedPermissions = [];

    public function mount()
    {
        // Retrieve all permissions to display in the view.
        $this->permissions = Permission::all(['id', 'name']);
    }
    /**
     * Handles the creation of a new role with assigned permissions.
     *
     * @return void
     */
    public function storeRole()
    {
        // Validate input fields using attribute rules
        $this->validate();

        try {
            // Create the role
            $role = Role::create(['name' => $this->name]);

            // Sync selected permissions to the newly created role
            $role->syncPermissions($this->selectedPermissions);

            // Clear form fields
            $this->resetInputFields();

            // Show success message and redirect
            $this->success('Role Added Successfully', redirectTo: '/admin/role/list');
        } catch (\Throwable $th) {
            // Show error message (detailed if in debug mode)
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    /**
     * Resets input fields to default state.
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
    <x-header title="Add New Role" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Role List" icon="o-plus" link="/admin/role/list" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-form wire:submit="storeRole">
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
                <x-button type="submit" label="Save Role" class="btn-primary btn-sm" />
            </x-slot:actions>
        </x-card>
    </x-form>
</div>
