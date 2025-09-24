<?php

use App\Models\Visa;
use App\Enum\VisaType;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Enum\VisaStatus;
use App\Enum\CountryStatus;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Traits\InteractsWithImageUploads;
use Livewire\Features\SupportFileUploads\WithFileUploads;

new #[Layout('components.layouts.admin')] #[Title('Visa Edit')] class extends Component {
    use Toast, WithFileUploads, InteractsWithImageUploads;

    public $countries = [];
    public $destinationCountries = [];
    public Visa $visa;
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
    public $type;

    #[Rule('required')]
    public $status;

    public function mount($visa)
    {
        $this->visa = $visa;
        $this->countries = Country::all();
        $this->destinationCountries = Country::all();
        $this->title = $visa->title;
        $this->sku_code = $visa->sku_code;
        $this->origin_country = $visa->origin_country;
        $this->destination_country = $visa->destination_country;
        $this->processing_time = $visa->processing_time;
        $this->convenient_fee = $visa->convenient_fee;
        $this->basic_info = $visa->basic_info;
        $this->depurture_requirements = $visa->depurture_requirements;
        $this->destination_requirements = $visa->destination_requirements;
        $this->checklists = $visa->checklists;
        $this->faq = $visa->faq;
        $this->type = $visa->type;
        $this->status = $visa->status;
        $this->types = VisaType::getVisaTypes();
        $this->statuses = VisaStatus::getStatuses();
        $this->rows = $visa->visaFees->isNotEmpty()
            ? $visa->visaFees
                ->map(function ($visa) {
                    return [
                        'id' => $visa->id,
                        'fee_type' => $visa->fee_type,
                        'fee' => $visa->fee,
                    ];
                })
                ->toArray()
            : [['fee_type' => null, 'fee' => null]];
    }
    public function addVisaFee()
    {
        $this->rows[] = [
            'fee_type' => null,
            'fee' => null,
        ];
    }
    public function removeRow($index)
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);

        if (empty($this->rows)) {
            $this->rows[] = ['fee_type' => null, 'fee' => null];
        }
    }
    public function countrySearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';

        $citizen = Country::where('status', CountryStatus::Active)->where('name', 'like', $searchTerm)->limit(5)->get();

        $this->countries = $citizen;
    }
    public function destinationCountrySearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';

        $citizen = Country::where('status', CountryStatus::Active)->where('name', 'like', $searchTerm)->limit(5)->get();

        $this->destinationCountries = $citizen;
    }
    public function updateVisa()
    {
        DB::beginTransaction();
        $this->validate();

        try {
            $storedApplicationFormPath = null;

            if ($this->application_form) {
                $storedApplicationFormPath = $this->optimizeAndUpdateImage(
                    $this->application_form,
                    $this->visa->application_form, // old path
                    'public',
                    'visa',
                    null,
                    null,
                    75,
                );
            } else {
                $storedApplicationFormPath = $this->visa->application_form;
            }

            $this->visa->update([
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
                'status' => $this->status,
                'action_id' => auth()->user()->id,
            ]);

            // Fetch existing IDs properly
            $existingIds = $this->visa->visaFees()->pluck('id')->toArray();
            $updatedIds = [];

            foreach ($this->rows as $row) {
                if (!isset($row['fee_type']) || !isset($row['fee'])) {
                    continue; // Skip invalid entries
                }

                if (isset($row['id'])) {
                    $price = $this->visa->visaFees()->find($row['id']);
                    if ($price) {
                        $price->update([
                            'fee_type' => $row['fee_type'],
                            'fee' => $row['fee'],
                        ]);
                        $updatedIds[] = $row['id'];
                    }
                } else {
                    $newVisaFee = $this->visa->visaFees()->create([
                        'fee_type' => $row['fee_type'],
                        'fee' => $row['fee'],
                    ]);
                    $updatedIds[] = $newVisaFee->id;
                }
            }

            // Delete fees that were removed
            $idsToDelete = array_diff($existingIds, $updatedIds);
            if (!empty($idsToDelete)) {
                $this->visa->visaFees()->whereIn('id', $idsToDelete)->delete();
            }

            DB::commit();
            $this->success('Visa Updated Successfully', redirectTo: '/admin/visa/list');
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div>
    @push('custom-script')
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    @endpush
    <x-header title="Update - {{ $visa->title }}" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Visa List" icon="fas.arrow-left" link="/admin/visa/list" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-form wire:submit="updateVisa">
        <div class="grid grid-cols-3 gap-4 items-start">
            <div class="col-span-2">
                <x-card>
                    <x-devider title="Visa Information" />
                    <x-input label="Visa Title" class="mb-2" wire:model="title" placeholder="Visa Title" required />
                    <div wire:ignore>
                        <label for="basic_info" class="font-normal text-sm">Basic Information</label>
                        <textarea wire:model="basic_info" id="basic_info" cols="30" rows="10">{{ $visa->basic_info }}</textarea>
                    </div>
                </x-card>
                <x-card class="mt-4">
                    <x-devider title="Depurture Requirements" />
                    <div wire:ignore>
                        <label for="depurture_requirements" class="font-normal text-sm">Depurture Requirements</label>
                        <textarea wire:model="depurture_requirements" id="depurture_requirements" cols="30" rows="10">{{ $visa->depurture_requirements }}</textarea>
                    </div>
                    <div wire:ignore class="mt-2">
                        <label for="destination_requirements" class="font-normal text-sm">Destination
                            Requirements</label>
                        <textarea wire:model="destination_requirements" id="destination_requirements" cols="30" rows="10">{{ $visa->destination_requirements }}</textarea>
                    </div>
                </x-card>
                <x-card class="mt-4">
                    <x-devider title="Other Requirements" />
                    <div wire:ignore>
                        <label for="checklists" class="font-normal text-sm">Checklists</label>
                        <textarea wire:model="checklists" id="checklists" cols="30" rows="10">{{ $visa->checklists }}</textarea>
                    </div>
                    <div wire:ignore class="mt-2">
                        <label for="faq" class="font-normal text-sm">FAQ</label>
                        <textarea wire:model="faq" id="faq" cols="30" rows="10">{{ $visa->faq }}</textarea>
                    </div>
                </x-card>
            </div>
            <x-card class="col-span-1" x-cloak>
                <x-devider title="Additional Information" />
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <x-choices label="Visa Type" :options="$types" single wire:model="type" required />
                    <x-choices label="Visa Status" :options="$statuses" single wire:model="status" placeholder="Select Status" required />
                    <x-choices label="Origin Country" :options="$countries" single wire:model.live="origin_country" placeholder="Select One" required
                        search-function="countrySearch" searchable />
                    <x-choices label="Destination Country" :options="$destinationCountries" single wire:model.live="destination_country" placeholder="Select One"
                        required search-function="destinationCountrySearch" searchable />
                    <x-input label="Processing Time" wire:model="processing_time" placeholder="30 Days" type="number" required />
                    <x-input label="Coinvenient Fee" wire:model="convenient_fee" placeholder="50000" type="number" required />
                    <x-input label="SKU" wire:model="sku_code" placeholder="SKU" required />
                </div>
                <x-file label="Application Form" wire:model="application_form" />

                <x-devider title="Visa Fees" />
                @foreach ($rows as $index => $row)
                    <div x-cloak class="grid grid-cols-2 mt-4 gap-2">
                        <x-input wire:model.live="rows.{{ $index }}.fee_type" placeholder="Visa Fee Type" />
                        <div class="flex gap-2">
                            <x-input wire:model.live="rows.{{ $index }}.fee" placeholder="Visa Fee" type="number" />
                            @if ($index == 0)
                                <x-icon class="cursor-pointer text-green-700 w-4" name="fas.circle-plus" wire:click="addVisaFee" />
                            @endif
                            <x-icon class="cursor-pointer text-red-700 w-4" name="fas.circle-xmark" wire:click="removeRow({{ $index }})" />
                        </div>
                    </div>
                @endforeach

                <x-slot:actions>
                    <x-button label="Visa List" link="/admin/visa/list" class="btn-sm" />
                    <x-button type="submit" label="Update Visa" class="btn-primary btn-sm" />
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
