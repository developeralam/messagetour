<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\AboutUs;

new #[Layout('components.layouts.app')] #[Title('About Us')] class extends Component {
    public $description;

    public function mount()
    {
        $data = AboutUs::first();
        if ($data) {
            $this->description = $data->description;
        }
    }
}; ?>

<div class="bg-gray-50 px-4 sm:px-6 lg:px-8 py-10">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-lg shadow-xl ring-1 ring-gray-200 overflow-hidden animate-fade-in-up">
            <div class="px-6 py-8 sm:p-10">
                <div class="mb-6 text-center">
                    <h2 class="text-2xl md:text-3xl lg:text-5xl font-bold tracking-tight">About Us</h2>
                </div>

                <div class="prose prose-green max-w-none text-gray-700">
                    {!! $description !!}
                </div>
            </div>
        </div>
    </div>
</div>
