<?php

use App\Enum\MailPort;
use Mary\Traits\Toast;
use App\Enum\MailMailer;
use App\Enum\MailEncryption;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use App\Models\GlobalSettings;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] #[Title('Global Settings')] class extends Component {
    use Toast, WithPagination;
    public $member;
    public $mailers = [];
    public $ports = [];
    public $encryptions = [];

    #[Rule('required')]
    public $mail_mailer;

    #[Rule('required')]
    public $mail_host;

    #[Rule('required')]
    public $mail_port;

    #[Rule('required')]
    public $mail_username;

    #[Rule('required')]
    public $mail_password;

    #[Rule('required')]
    public $mail_encryption;

    #[Rule('required')]
    public $mail_from_address;

    #[Rule('required')]
    public $mail_from_name;

    public function mount()
    {
        $data = GlobalSettings::first();
        $this->mailers = MailMailer::getMailers();
        $this->ports = MailPort::getPorts();
        $this->encryptions = MailEncryption::getEncryption();
        if ($data) {
            $this->mail_mailer = $data->mail_mailer;
            $this->mail_host = $data->mail_host;
            $this->mail_port = $data->mail_port;
            $this->mail_username = $data->mail_username;
            $this->mail_encryption = $data->mail_encryption;
            $this->mail_from_address = $data->mail_from_address;
            $this->mail_from_name = $data->mail_from_name;
        }
    }

    public function storeMailSettings()
    {
        $this->validate();

        try {
            $globalSetting = GlobalSettings::first() ?? new GlobalSettings();
            $globalSetting->mail_mailer = $this->mail_mailer;
            $globalSetting->mail_host = $this->mail_host;
            $globalSetting->mail_port = $this->mail_port;
            $globalSetting->mail_username = $this->mail_username;
            $globalSetting->mail_password = $this->mail_password;
            $globalSetting->mail_encryption = $this->mail_encryption;
            $globalSetting->mail_from_address = $this->mail_from_address;
            $globalSetting->mail_from_name = $this->mail_from_name;

            $globalSetting->save();

            $this->success('Mail Settings Updated Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div>
    <livewire:global-settings-component />
    <x-form wire:submit="storeMailSettings">
        <x-card>
            <x-devider title="Mail Information" />
            <div class="grid grid-cols-3 gap-4" x-cloak>
                <x-choices wire:model="mail_mailer" single :options="$mailers" label="Mail Mailer" placeholder="Select One"
                    required />
                <x-input wire:model="mail_host" label="Mail Host" required placeholder="Mail Host" />
                <x-choices wire:model="mail_port" single label="Mail Port" placeholder="Select One" :options="$ports"
                    required />
                <x-input wire:model="mail_username" label="Mail Username" required placeholder="Mail Username" />
                <x-password wire:model="mail_password" label="Mail Password" required placeholder="Mail Password" right />
                <x-choices wire:model="mail_encryption" single label="Mail Encryption" placeholder="Select One"
                :options="$encryptions" required />
                <x-input wire:model="mail_from_address" label="Mail From Address" required placeholder="Mail From Address" />
                <x-input wire:model="mail_from_name" label="Mail From Name" required placeholder="Mail From Name" />
            </div>
            <x-slot:actions>
                <x-button type="submit" label="Save" class="btn-primary btn-sm" />
            </x-slot:actions>
        </x-card>
    </x-form>
</div>
