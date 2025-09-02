<?php

use Mary\Traits\Toast;
use App\Models\AboutUs;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] #[Title('About Us')] class extends Component {
    use Toast;

    #[Rule('nullable')]
    public $description;

    public function mount()
    {
        $data = AboutUs::first();
        if ($data) {
            $this->description = $data->description;
        }
    }

    public function save()
    {
        $this->validate([
            'description' => 'nullable|string',
        ]);
        $aboutus = AboutUs::first() ?? new AboutUs();

        $aboutus->description = $this->description;
        $aboutus->save();
        $this->success('About Us Updated Successfully');
    }
}; ?>

<div>
    @push('custom-script')
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    @endpush
    <x-form wire:submit="storeGlobalData">
        <x-card>
            <x-devider title="About Us Information" />
            <div wire:ignore>
                <textarea wire:model="description" id="description">{!! $description !!}</textarea>
            </div>
            <x-slot:actions>
                <x-button type="submit" label="Store Global Data" spinner="storeGlobalData" class="btn-primary btn-sm" />
            </x-slot>
        </x-card>
    </x-form>
    @push('custom-script')
        <script>
            ClassicEditor
                .create(document.querySelector('#description'))
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                        @this.set('description', editor.getData());
                    })
                })
                .catch(error => {
                    console.error(error);
                });
        </script>
    @endpush
</div>
