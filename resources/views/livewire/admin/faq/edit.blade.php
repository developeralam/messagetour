<?php

use App\Models\Faq;
use App\Models\Offer;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] #[Title('Update Faq')] class extends Component {
    use Toast;

    public $offers;

    #[Rule('required')]
    public $offer_id;

    #[Rule('required')]
    public $question;

    #[Rule('required')]
    public $answer;

    // Bind the Faq model instance to the component
    public Faq $faq;

    /**
     * Mount lifecycle hook.
     * Initializes the component with data from the provided FAQ model instance.
     *
     * @param Faq $faq
     * @return void
     */
    public function mount(Faq $faq): void
    {
        // Assign the bound FAQ model to the component property
        $this->faq = $faq;

        // Fetch all offers to populate the dropdown
        $this->offers = Offer::select(['id', 'title'])->get();

        // Pre-fill form fields with existing FAQ data
        $this->offer_id = $faq->offer_id;
        $this->question = $faq->question;
        $this->answer = $faq->answer;
    }

    /**
     * Update the existing FAQ entry in the database.
     * Validates the input and attempts to update the model.
     *
     * @return void
     */
    public function updateFaq(): void
    {
        // Validate the component's form data
        $this->validate();

        try {
            // Update the FAQ record with new question and answer
            $this->faq->update([
                'offer_id' => $this->offer_id,
                'question' => $this->question,
                'answer' => $this->answer,
                'action_by' => auth()->user()->id,
            ]);

            // Show success message and redirect to the FAQ list page
            $this->success('Faq Updated Successfully', redirectTo: '/admin/faq/list');
        } catch (\Throwable $th) {
            // Handle any exceptions with debug message if enabled
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div>
    @push('custom-script')
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    @endpush

    <x-header title="Update FAQ" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Back" link="/admin/faq/list" class="btn-sm btn-primary" icon="fas.arrow-left" />
        </x-slot>
    </x-header>
    <x-form wire:submit="updateFaq">
        <x-card x-cloak>
            <x-choices wire:model="offer_id" :options="$offers" option-label="title" option-value="id"
                placeholder="Select Offer" single label="Offer" required />
            <div wire:ignore class="mt-2">
                <label for="question" class="font-semibold text-sm">Question</label>
                <textarea wire:model="question" id="question" cols="30" rows="10">{{ $faq->question }}</textarea>
            </div>
            @error('question')
                <span class="text-red-500 font-normal text-sm">{{ $message }}</span>
            @enderror

            <div wire:ignore class="mt-2">
                <label for="answer" class="font-semibold text-sm">Answer</label>
                <textarea wire:model="answer" id="answer" cols="30" rows="10">{{ $faq->answer }}</textarea>
            </div>
            @error('answer')
                <span class="text-red-500 font-normal text-sm">{{ $message }}</span>
            @enderror

            <x-slot:actions>
                <x-button label="Cancel" type="reset" class="btn-sm" />
                <x-button type="submit" label="update" class="btn-primary btn-sm" spinner="updateFaq" />
            </x-slot>
        </x-card>
    </x-form>
    @push('custom-script')
        <script>
            ClassicEditor
                .create(document.querySelector('#question'))
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                        @this.set('question', editor.getData());
                    })
                })
                .catch(error => {
                    console.error(error);
                });
            ClassicEditor
                .create(document.querySelector('#answer'))
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                        @this.set('answer', editor.getData());
                    })
                })
                .catch(error => {
                    console.error(error);
                });
        </script>
    @endpush
</div>
