<?php

use App\Models\Offer;
use App\Models\Coupon;
use Mary\Traits\Toast;
use App\Enum\OfferType;
use App\Enum\OfferStatus;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Traits\InteractsWithImageUploads;
use Livewire\Features\SupportFileUploads\WithFileUploads;

new #[Layout('components.layouts.admin')] #[Title('Update Offer')] class extends Component {
    use Toast, WithFileUploads, InteractsWithImageUploads;
    public $offer = [];

    #[Rule('required')]
    public $title;

    #[Rule('required')]
    public $type;

    #[Rule('nullable')]
    public $coupon_id;

    #[Rule('required')]
    public $link;

    #[Rule('required')]
    public $applicable_users;

    #[Rule('required')]
    public $avail_this_offer_step_1;

    #[Rule('required')]
    public $avail_this_offer_step_2;

    #[Rule('required')]
    public $avail_this_offer_step_3;

    #[Rule('required')]
    public $description;

    #[Rule('required')]
    public $thumbnail;

    #[Rule('required')]
    public $status;

    public $coupons;
    public $statusOptions;
    public $validaty;
    public $offerTypes;

    public function mount(Offer $offer)
    {
        if ($offer) {
            $this->offer = $offer;
            $this->title = $offer->title;
            $this->type = $offer->type;
            $this->description = $offer->description;
            $this->status = $offer->status;
            $this->coupon_id = $offer->coupon_id;
            $this->link = $offer->link;
            $this->applicable_users = $offer->applicable_users;
            $this->avail_this_offer_step_1 = $offer->avail_this_offer_step_1;
            $this->avail_this_offer_step_2 = $offer->avail_this_offer_step_2;
            $this->avail_this_offer_step_3 = $offer->avail_this_offer_step_3;
        }
        $this->coupons = Coupon::select('code', 'id')->get();
        $this->statusOptions = OfferStatus::getOfferStatuses();
        $this->offerTypes = OfferType::getOfferTypes();
    }
    public function updateOffer()
    {
        $this->validate();
        try {
            $updatedThumbnailPath = $this->thumbnail ? $this->optimizeAndUpdateImage($this->thumbnail, $this->offer->thumbnail, 'public', 'offer', null, null, 75) : $this->offer->thumbnail;

            $this->offer->update([
                'title' => $this->title,
                'slug' => Str::slug($this->title),
                'validaty' => $this->validaty,
                'description' => $this->description,
                'coupon_id' => $this->coupon_id,
                'link' => $this->link,
                'applicable_users' => $this->applicable_users,
                'avail_this_offer_step_1' => $this->avail_this_offer_step_1,
                'avail_this_offer_step_2' => $this->avail_this_offer_step_2,
                'avail_this_offer_step_3' => $this->avail_this_offer_step_3,
                'thumbnail' => $updatedThumbnailPath,
                'created_by' => auth()->user()->id,
                'status' => $this->status,
                'action_id' => auth()->user()->id,
            ]);
            $this->success('Offer Updated Successfully', redirectTo: '/admin/offer/list');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div>
    @push('custom-script')
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    @endpush

    <x-header title="Update - {{ $offer->title }}" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Offer List" icon="fas.arrow-left" link="/admin/offer/list" class="btn-primary btn-sm" />
        </x-slot>
    </x-header>
    <x-form wire:submit="updateOffer">
        <div class="grid grid-cols-3 gap-4 items-start" x-cloak>
            <div class="col-span-2">
                <x-card>
                    <x-devider title="Offer Information" />
                    <div
                        class="{{ $type == \App\Enum\OfferType::Coupon ? 'grid grid-cols-4 gap-4 mb-4' : 'grid grid-cols-2 gap-4 mb-4' }}">
                        <x-input label="Offer Title" wire:model="title" placeholder="Offer Title" required />
                        <x-choices label="Offer Type" wire:model.live="type" :options="$offerTypes" placeholder="Select Type"
                            required single />
                        @if ($type == \App\Enum\OfferType::Coupon)
                            <x-choices label="Coupon" wire:model.live="coupon_id" :options="$coupons"
                                placeholder="Select Coupon" option-label="code" option-value="id" required single />
                            <x-datetime label="Offer Validaty" wire:model="validaty" required readonly />
                        @endif
                    </div>
                    <x-devider title="How to avail this offer Information" />
                    <div wire:ignore>
                        <label for="avail_this_offer_step_1" class="font-normal text-sm">How to avail this offer
                            Step 1</label>
                        <textarea wire:model="avail_this_offer_step_1" id="avail_this_offer_step_1" cols="30" rows="10" required>{{ $offer->avail_this_offer_step_1 }}</textarea>
                    </div>
                    <div wire:ignore class="mt-4">
                        <label for="avail_this_offer_step_2" class="font-normal text-sm">How to avail this offer
                            Step 2</label>
                        <textarea wire:model="avail_this_offer_step_2" id="avail_this_offer_step_2" cols="30" rows="10" required>{{ $offer->avail_this_offer_step_2 }}</textarea>
                    </div>
                    <div wire:ignore class="mt-4">
                        <label for="avail_this_offer_step_3" class="font-normal text-sm">How to avail this offer
                            Step 3</label>
                        <textarea wire:model="avail_this_offer_step_3" id="avail_this_offer_step_3" cols="30" rows="10" required>{{ $offer->avail_this_offer_step_3 }}</textarea>
                    </div>
                    <x-devider title="Terms & Conditions" />
                    <div wire:ignore class="mb-4">
                        <label for="description" class="font-normal text-sm">Terms & Conditions</label>
                        <textarea wire:model="description" id="description" cols="30" rows="10" required>{{ $offer->description }}</textarea>
                    </div>
                </x-card>
            </div>
            <x-card class="col-span-1">
                <x-devider title="Additional Information" />
                <x-textarea label="Applicable Users" wire:model="applicable_users" placeholder="Applicable Users"
                    cols="30" rows="1" class="mb-2" />
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <x-input label="Link" wire:model="link" placeholder="https://example.com" />
                    <x-choices label="Offer Status" :options="$statusOptions" wire:model="status" required single />
                </div>
                <x-file class="mb-2" label="Thumbnail" wire:model="thumbnail" accept="image/png, image/jpeg">
                    <img src="{{ $offer->thumbnail_link ?? asset('/empty-user.jpg') }}" alt=""
                        class="h-40 rounded-lg" />
                </x-file>
                <x-menu-separator />

                <x-slot:actions>
                    <x-button label="Offer List" link="/admin/offer/list" class="btn-sm" />
                    <x-button type="submit" label="Update Offer" class="btn-primary btn-sm" spinner="updateOffer" />
                </x-slot>
            </x-card>
        </div>
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
            ClassicEditor
                .create(document.querySelector('#avail_this_offer_step_1'))
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                        @this.set('avail_this_offer_step_1', editor.getData());
                    })
                })
                .catch(error => {
                    console.error(error);
                });
            ClassicEditor
                .create(document.querySelector('#avail_this_offer_step_2'))
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                        @this.set('avail_this_offer_step_2', editor.getData());
                    })
                })
                .catch(error => {
                    console.error(error);
                });
            ClassicEditor
                .create(document.querySelector('#avail_this_offer_step_3'))
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                        @this.set('avail_this_offer_step_3', editor.getData());
                    })
                })
                .catch(error => {
                    console.error(error);
                });
        </script>
    @endpush
</div>
