<?php

use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Role List')] class extends Component {
    use WithPagination;
    use Toast;
    public array $headers;
    public string $search = '';
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];

    /**
     * Initializes component state.
     *
     * @return void
     */
    public function mount()
    {
        // Load table headers
        $this->headers = $this->headers();
    }

    /**
     * Deletes the given role.
     *
     * @param Spatie\Permission\Models\Role $role
     * @return void
     */
    public function delete(Role $role)
    {
        try {
            $role->delete();

            // Show success message
            $this->success('Role Deleted Successfully');
        } catch (\Throwable $th) {
            // Show error message (detailed if debug mode enabled)
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    /**
     * Returns the table headers configuration.
     *
     * @return array
     */
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'name', 'label' => 'Role Name']];
    }

    /**
     * Retrieves the paginated list of roles, with optional search filtering.
     *
     * @return Livewire\WithPagination
     */
    public function roles()
    {
        return Role::query()->when($this->search, fn(Builder $q) => $q->where('name', 'like', '%' . $this->search . '%'))->paginate(10);
    }

    /**
     * Provides additional data to be passed to the view.
     *
     * @return array
     */
    public function with(): array
    {
        return [
            'roles' => $this->roles(),
        ];
    }
}; ?>

<div>
    <x-header title="Role List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Add Role" icon="o-plus" link="/admin/role/create" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$roles" :sort-by="$sortBy" with-pagination>
            @scope('actions', $role)
                @if ($role->name != 'Super Admin')
                    <div class="flex items-center gap-1">
                        <x-button icon="o-trash" wire:click="delete({{ $role['id'] }})" wire:confirm="Are you sure?"
                            class="btn-action btn-error" spinner="delete({{ $role['id'] }})" />

                        <x-button icon="s-pencil-square" link="/admin/role/{{ $role['id'] }}/edit"
                            class="btn-action btn-neutral" />
                    </div>
                @endif
            @endscope
        </x-table>
    </x-card>
</div>
