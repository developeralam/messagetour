<?php

use App\Models\Visa;
use App\Enum\VisaType;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Models\VisaFee;
use App\Enum\VisaStatus;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Traits\InteractsWithImageUploads;
use Livewire\Features\SupportFileUploads\WithFileUploads;

new #[Layout('components.layouts.admin')] #[Title('Add New Visa')] class extends Component {
    use Toast, WithFileUploads, InteractsWithImageUploads;

    public Collection $countries;
    public $types = [];
    public $statuses = [];
    public $rows = [];

    #[Rule('required')]
    public $title;

    #[Rule('required')]
    public $sku_code;

    #[Rule('required')]
    public $origin_country;

    #[Rule('required')]
    public $destination_country;

    #[Rule('required')]
    public $processing_time;

    #[Rule('nullable')]
    public $application_form;

    #[Rule('required')]
    public $convenient_fee;

    #[Rule('nullable')]
    public $basic_info;

    #[Rule('nullable')]
    public $depurture_requirements;

    #[Rule('nullable')]
    public $destination_requirements;

    #[Rule('nullable')]
    public $checklists;

    #[Rule('nullable')]
    public $faq;

    #[Rule('required')]
    public $type = VisaType::Evisa;

    #[Rule('required')]
    public $status = VisaStatus::Active;

    public $fee_type;
    public $fee;

    public function mount()
    {
        $this->countries = Country::all();
        $this->types = VisaType::getVisaTypes();
        $this->statuses = VisaStatus::getStatuses();
        $this->rows[] = [
            'fee_type' => null,
            'fee' => null,
        ];
    }
    public function addVisaFee()
    {
        $this->rows[] = [
            'fee_type' => null,
            'fee' => null,
        ];
    }
    public function storeVisa()
    {
        DB::beginTransaction();
        $this->validate();
        try {
            $storedApplicationFormPath = null;
            if ($this->application_form) {
                $storedApplicationFormPath = $this->optimizeAndStoreImage(
                    $this->application_form, // The file from Livewire
                    'public', // The disk to store on
                    'visa', // The subdirectory within the disk
                    null, // Optional max width
                    null, // Optional max height
                    75, // WEBP quality
                );
            }
            $visa = Visa::create([
                'title' => $this->title,
                'slug' => Str::slug($this->title),
                'sku_code' => $this->sku_code,
                'origin_country' => $this->origin_country,
                'destination_country' => $this->destination_country,
                'processing_time' => $this->processing_time,
                'application_form' => $storedApplicationFormPath,
                'convenient_fee' => $this->convenient_fee,
                'basic_info' => $this->basic_info,
                'depurture_requirements' => $this->depurture_requirements,
                'destination_requirements' => $this->destination_requirements,
                'checklists' => $this->checklists,
                'faq' => $this->faq,
                'type' => $this->type,
                'created_by' => auth()->user()->id,
                'status' => $this->status,
            ]);
            foreach ($this->rows as $row) {
                if ($row) {
                    VisaFee::create([
                        'visa_id' => $visa->id,
                        'fee_type' => $row['fee_type'],
                        'fee' => $row['fee'],
                    ]);
                }
            }
            DB::commit();
            $this->success('Visa Added Successfully', redirectTo: '/admin/visa/list');
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    public function removeRow($index)
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
    }
}; ?>

<div>
    @push('custom-script')
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    @endpush
    <x-header title="Add New Visa" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Visa List" icon="fas.arrow-left" link="/admin/visa/list" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-form wire:submit="storeVisa">
        <div class="grid grid-cols-3 gap-4 items-start">
            <div class="col-span-2">
                <x-card>
                    <x-devider title="Visa Information" />
                    <x-input label="Visa Title" class="mb-2" wire:model="title" placeholder="Visa Title" required />
                    <div wire:ignore>
                        <label for="basic_info" class="font-normal text-sm">Basic Information</label>
                        <textarea wire:model="basic_info" id="basic_info" cols="30" rows="10"></textarea>
                    </div>
                </x-card>
                <x-card class="mt-4">
                    <x-devider title="Depurture Requirements" />
                    <div wire:ignore>
                        <label for="depurture_requirements" class="font-normal text-sm">Depurture Requirements</label>
                        <textarea wire:model="depurture_requirements" id="depurture_requirements" cols="30" rows="10"></textarea>
                    </div>
                    <div wire:ignore class="mt-2">
                        <label for="destination_requirements" class="font-normal text-sm">Destination
                            Requirements</label>
                        <textarea wire:model="destination_requirements" id="destination_requirements" cols="30" rows="10"></textarea>
                    </div>
                </x-card>
                <x-card class="mt-4">
                    <x-devider title="Other Requirements" />
                    <div wire:ignore>
                        <label for="checklists" class="font-normal text-sm">Checklists</label>
                        <textarea wire:model="checklists" id="checklists" cols="30" rows="10"></textarea>
                    </div>
                    <div wire:ignore class="mt-2">
                        <label for="faq" class="font-normal text-sm">FAQ</label>
                        <textarea wire:model="faq" id="faq" cols="30" rows="10"></textarea>
                    </div>
                </x-card>
            </div>
            <x-card class="col-span-1" x-cloak>
                <x-devider title="Additional Information" />
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <x-choices label="Visa Type" :options="$types" single wire:model="type" required />
                    <x-choices label="Visa Status" :options="$statuses" single wire:model="status"
                        placeholder="Select Status" required />
                    <x-choices label="Origin Country" :options="$countries" single wire:model="origin_country"
                        placeholder="Select One" required />
                    <x-choices label="Destination Country" :options="$countries" single wire:model="destination_country"
                        placeholder="Select One" required />
                    <x-input label="Processing Time" wire:model="processing_time" placeholder="30 Days" type="number"
                        required />
                    <x-input label="Coinvenient Fee" wire:model="convenient_fee" placeholder="50000" type="number"
                        required />
                    <x-input label="SKU" wire:model="sku_code" placeholder="SKU" required />
                </div>
                <x-file label="Application Form" wire:model="application_form" />

                <x-devider title="Visa Fees" />
                @foreach ($rows as $index => $row)
                    <div x-cloak class="grid grid-cols-2 mt-4 gap-2">
                        <x-input wire:model.live="rows.{{ $index }}.fee_type" placeholder="Visa Fee Type" />
                        <div class="flex gap-2">
                            <x-input wire:model.live="rows.{{ $index }}.fee" placeholder="Visa Fee"
                                type="number" />
                            @if ($index == 0)
                                <x-icon class="cursor-pointer text-green-700 w-4" name="fas.circle-plus"
                                    wire:click="addVisaFee" />
                            @endif
                            <x-icon class="cursor-pointer text-red-700 w-4" name="fas.circle-xmark"
                                wire:click="removeRow({{ $index }})" />
                        </div>
                    </div>
                @endforeach

                <x-slot:actions>
                    <x-button label="Visa List" link="/admin/visa/list" class="btn-sm" />
                    <x-button type="submit" label="Add Visa" class="btn-primary btn-sm" />
                </x-slot:actions>

            </x-card>
        </div>
    </x-form>
    @push('custom-script')
        <script>
            ClassicEditor
                .create(document.querySelector('#basic_info'))
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                        @this.set('basic_info', editor.getData());
                    })
                })
                .catch(error => {
                    console.error(error);
                });
            ClassicEditor
                .create(document.querySelector('#depurture_requirements'))
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                        @this.set('depurture_requirements', editor.getData());
                    })
                })
                .catch(error => {
                    console.error(error);
                });
            ClassicEditor
                .create(document.querySelector('#destination_requirements'))
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                        @this.set('destination_requirements', editor.getData());
                    })
                })
                .catch(error => {
                    console.error(error);
                });
            ClassicEditor
                .create(document.querySelector('#checklists'))
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                        @this.set('checklists', editor.getData());
                    })
                })
                .catch(error => {
                    console.error(error);
                });
            ClassicEditor
                .create(document.querySelector('#faq'))
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                        @this.set('faq', editor.getData());
                    })
                })
                .catch(error => {
                    console.error(error);
                });
        </script>
    @endpush
</div>
