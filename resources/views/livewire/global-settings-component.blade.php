<div class="mb-2 bg-white p-2">
    <x-button label="Global Data" link="/admin/global-settings/"
        class="btn-sm {{ url()->current() == url('/admin/global-settings/') ? 'bg-green-500' : 'btn-primary' }}" />

    <x-button label="Email Settings" link="/admin/global-settings/email"
        class="btn-sm {{ url()->current() == url('/admin/global-settings/email') ? 'bg-green-500' : 'btn-primary' }}" />
</div>
