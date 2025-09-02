<?php

use App\Models\GlobalSettings;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new #[Layout('components.layouts.admin')] #[Title('Global Settings')] class extends Component
{
    use Toast, WithFileUploads;

    public $logo_link;

    public $favicon_link;

    #[Rule('nullable')]
    public $logo;

    #[Rule('nullable')]
    public $favicon;

    #[Rule('nullable')]
    public $contact_email;

    #[Rule('nullable')]
    public $support_email;

    #[Rule('nullable')]
    public $address;

    #[Rule('nullable')]
    public $phone;

    #[Rule('nullable')]
    public $facebook_url;

    #[Rule('nullable')]
    public $linkedin_url;

    #[Rule('nullable')]
    public $instagram_url;

    #[Rule('nullable')]
    public $twitter_url;

    #[Rule('nullable')]
    public $sms_api_key;

    #[Rule('nullable')]
    public $sms_sender_id;

    #[Rule('nullable')]
    public $reservation;

    #[Rule('nullable')]
    public $reservation_email;

    #[Rule('nullable')]
    public $account;

    #[Rule('nullable')]
    public $account_email;

    public function mount()
    {
        $data = GlobalSettings::first();
        if ($data) {
            $this->logo_link = $data->logo_link;
            $this->favicon_link = $data->favicon_link;
            $this->contact_email = $data->contact_email;
            $this->support_email = $data->support_email;
            $this->phone = $data->phone;
            $this->address = $data->address;
            $this->facebook_url = $data->facebook_url;
            $this->linkedin_url = $data->linkedin_url;
            $this->instagram_url = $data->instagram_url;
            $this->twitter_url = $data->twitter_url;
            $this->sms_api_key = $data->sms_api_key;
            $this->sms_sender_id = $data->sms_sender_id;
            $this->reservation = $data->reservation;
            $this->reservation_email = $data->reservation_email;
            $this->account = $data->account;
            $this->account_email = $data->account_email;
        }
    }

    public function storeGlobalData()
    {
        $this->validate();
        try {
            $logo_url = $this->logo ? $this->logo->store('global', 'public') : null;
            $favicon_url = $this->favicon ? $this->favicon->store('global', 'public') : null;

            // Get the existing row or create a new one
            $globalSetting = GlobalSettings::first() ?? new GlobalSettings;

            // Update the attributes
            $globalSetting->logo = $logo_url ?? $globalSetting->logo;
            $globalSetting->favicon = $favicon_url ?? $globalSetting->favicon;
            $globalSetting->contact_email = $this->contact_email;
            $globalSetting->support_email = $this->support_email;
            $globalSetting->address = $this->address;
            $globalSetting->phone = $this->phone;
            $globalSetting->facebook_url = $this->facebook_url;
            $globalSetting->linkedin_url = $this->linkedin_url;
            $globalSetting->instagram_url = $this->instagram_url;
            $globalSetting->twitter_url = $this->twitter_url;
            $globalSetting->sms_api_key = $this->sms_api_key;
            $globalSetting->sms_sender_id = $this->sms_sender_id;
            $globalSetting->reservation = $this->reservation;
            $globalSetting->reservation_email = $this->reservation_email;
            $globalSetting->account = $this->account;
            $globalSetting->account_email = $this->account_email;

            // Save the data
            $globalSetting->save();
            Cache::forget('global_settings');

            $this->success('Global Settings Updated Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div>
    <livewire:global-settings-component />
    <x-form wire:submit="storeGlobalData">
        <x-card>
            <x-devider title="Global Informations"/>
            <div class="grid grid-cols-4 gap-4">
                <x-file wire:model="logo" label="Website Logo" accept="image/png, image/jpeg,image/jpg"/>
                <x-file wire:model="favicon" label="Favicon" accept="image/png, image/jpeg,image/jpg"/>
                <x-input type="email" wire:model="contact_email" label="Information Email" placeholder="Information Email" />
                <x-input type="email" wire:model="support_email" label="Support Email" placeholder="Support Email" />
                <x-input wire:model="phone" label="Phone" placeholder="Phone" />
                <x-input wire:model="address" label="Address" placeholder="Address" />
                <x-input wire:model="facebook_url" label="Facebook Url" placeholder="Facebook Url" />
                <x-input wire:model="linkedin_url" label="Linkedin Url" placeholder="Linkedin Url" />
                <x-input wire:model="instagram_url" label="Instagram Url" placeholder="Instagram Url" />
                <x-input wire:model="twitter_url" label="Twitter Url" placeholder="Twitter Url" />
                <x-input wire:model="sms_api_key" label="Sms Api Key" placeholder="Sms Api Key" />
                <x-input wire:model="sms_sender_id" label="Sms Sender Id" placeholder="Sms Sender Id" />
                <x-input wire:model="reservation" label="Reservation" placeholder="Reservation" />
                <x-input type="email" wire:model="reservation_email" label="Reservation Email" placeholder="Reservation Email" />
                <x-input wire:model="account" label="Account" placeholder="Account" />
                <x-input type="email" wire:model="account_email" label="Account Email" placeholder="Account Email" />
            </div>
            <x-slot:actions>
                <x-button type="submit" label="Store Global Data" spinner="storeGlobalData" class="btn-primary btn-sm" />
            </x-slot>
        </x-card>
    </x-form>
</div>