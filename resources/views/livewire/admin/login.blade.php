<?php

use App\Enum\UserType;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Mary\Traits\Toast;

new #[Layout('components.layouts.admin-auth')] #[Title('Admin Login')] class extends Component {
    use Toast;
    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required')]
    public string $password = '';

    public function mount()
    {
        // It is logged in
        if (auth()->user() && UserType::Admin) {
            return redirect('/admin/dashboard');
        }
    }
    public function login()
    {
        $credentials = $this->validate();

        if (auth()->attempt($credentials)) {
            request()->session()->regenerate();
            $this->success('Login Successfull.', redirectTo: '/admin/dashboard');
        }

        $this->addError('email', 'The provided credentials do not match our records.');
    }
}; ?>

<div class="flex min-h-screen items-center justify-center bg-gray-100 px-4 py-8">
    <div class="grid grid-cols-1 md:grid-cols-2 w-full max-w-4xl bg-white rounded-2xl shadow-lg overflow-hidden">
        <!-- Left Panel -->
        <div class="bg-green-500 text-white p-8 flex flex-col justify-center">
            <div class="mb-6">
                <img src="{{ asset('logo.png') }}" alt="FlyValy Logo">
            </div>
            <h2 class="text-2xl md:text-3xl font-bold mb-1">Welcome to FlyValy</h2>
            <p class="text-sm md:text-base leading-relaxed">
                Access powerful tools to manage your application operations.
            </p>
            <div class="mt-6">
                <a href="/"
                    class="inline-block border border-white font-semibold text-white px-4 py-2 rounded-full hover:bg-white hover:text-green-500 transition-all text-sm">
                    Visit Website
                </a>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="p-6 md:p-8 flex flex-col justify-center">
            <h2 class="text-xl md:text-2xl font-semibold text-green-600 mb-4 text-center md:text-left">Sign in to your
                Admin Dashboard</h2>

            <x-form wire:submit="login" class="space-y-1">
                {{-- Email --}}
                <x-input wire:model="email" placeholder="Email Address" icon="o-envelope"
                    class="w-full bg-gray-100 focus:ring-green-500 focus:border-green-500 rounded-md shadow-sm custome-input-field" />

                {{-- Password --}}
                <x-password wire:model="password" type="password" icon="o-lock-closed" placeholder="Password"
                    class="w-full bg-gray-100 focus:ring-green-500 focus:border-green-500 rounded-md shadow-sm custome-input-field"
                    right />

                <x-button label="Sign In" type="submit"
                    class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-2 rounded-md transition-all"
                    spinner="login" />
            </x-form>

            <div class="mt-6 text-center text-xs text-gray-400">
                &copy; {{ date('Y') }} <a href="/"
                    class="text-xs font-bold text-transparent bg-clip-text bg-gradient-to-r from-green-500 to-green-400 italic">Flyvaly</a>
                Traveling. All rights reserved.
            </div>
        </div>
    </div>
</div>
