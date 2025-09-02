<?php

use Mary\Traits\Toast;
use App\Models\ContactUs;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app')] #[Title('Contact Us')] class extends Component {
    use Toast;

    #[Rule('required')]
    public $first_name;

    #[Rule('required')]
    public $last_name;

    #[Rule('required')]
    public $email_address;

    #[Rule('nullable')]
    public $phone;

    #[Rule('nullable')]
    public $subject;

    #[Rule('required')]
    public $message;

    public function storeContactUs()
    {
        $this->validate();

        try {
            ContactUs::create([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email_address' => $this->email_address,
                'phone' => $this->phone,
                'subject' => $this->subject,
                'message' => $this->message,
            ]);
            $this->reset(['first_name', 'last_name', 'email_address', 'phone', 'subject', 'message']);
            $this->success('Send Your Message Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div class="bg-gray-100 px-4 sm:px-6 lg:px-8 py-10">
    <div class="max-w-6xl mx-auto bg-white rounded-lg shadow-xl px-6 py-8">
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-black mb-4">Contact Us</h2>
        <hr class="mb-6 border-gray-300">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Info -->
            <div>
                <p class="text-gray-700 text-base">
                    For any queries or complaints, we are always here for you!<br>
                    Please reach out to us at:
                </p>

                <div class="mt-6 flex justify-between md:flex-row md:justify-start md:gap-20">
                    <div>
                        <p class="text-sm md:text-base font-medium text-gray-500">Email us:</p>
                        <p class="md:text-base text-xs font-bold text-black break-all">
                            {{ $globalSettings->contact_email ?? '' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm md:text-base font-medium text-gray-500">Call us:</p>
                        <p class="md:text-base text-xs font-bold text-black">
                            {{ $globalSettings->phone ?? '' }}
                        </p>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-sm sm:text-base font-medium text-gray-500 mb-2">Our Location:</p>
                    <div class="w-full h-52 rounded-lg overflow-hidden shadow-md border-2 border-green-500">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3651.850965450922!2d90.37955307526468!3d23.753798588918343!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755b89f8d15e395%3A0x2c650b0e5c00a70c!2sMM%20IT%20SOFT%20LTD!5e0!3m2!1sen!2sbd!4v1699115043456!5m2!1sen!2sbd"
                            width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>

            <!-- Right Form -->
            <x-form wire:submit="storeContactUs">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="First Name" wire:model="first_name" class="custome-input-field py-4" required
                        placeholder="Write first name here" />

                    <x-input label="Last Name" wire:model="last_name" class="custome-input-field py-4" required
                        placeholder="Write last name here" />

                    <x-input type="email" label="Your Email" wire:model="email_address"
                        class="custome-input-field py-4" required placeholder="Write your email address here" />

                    <x-input type="tel" label="Your Phone" wire:model="phone" class="custome-input-field py-4"
                        placeholder="e.g. +88 01706668403" />
                </div>

                <div class="mt-2">
                    <x-input label="Your Subject" wire:model="subject" class="custome-input-field py-4"
                        placeholder="Write your subject here" />
                </div>

                <div class="mt-2">
                    <x-textarea label="Your Message" wire:model="message" class="custome-input-field" required
                        placeholder="Write your message here" rows="4" />
                </div>

                <div class="pt-2 text-right">
                    <button type="submit"
                        class="bg-gradient-to-r from-green-500 to-green-400 hover:from-green-600 hover:to-green-500 text-white font-semibold px-6 py-2 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                        Send Message
                    </button>
                </div>
            </x-form>
        </div>
    </div>
</div>
