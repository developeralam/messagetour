<?php

use App\Enum\UserType;
use Mary\Traits\Toast;
use App\Enum\AgentStatus;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin-auth')] #[Title('Partner Login')] class extends Component {
    use Toast;

    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required')]
    public string $password = '';

    public function mount()
    {
        // It is logged in
        if (auth()->user() && UserType::Agent) {
            return redirect('/partner/dashboard');
        }
    }
    public function login()
    {
        $credentials = $this->validate();

        // Check if the credentials match any user in the database
        $user = auth()->getProvider()->retrieveByCredentials($credentials);

        if ($user && auth()->attempt($credentials)) {
            request()->session()->regenerate();

            // Check if the authenticated user is an agent and their status
            if ($user->agent && $user->agent->status == AgentStatus::Approve) {
                $this->success('Login successful.', redirectTo: '/partner/dashboard');
            } else {
                // Logout the user if their status is not approved or not an agent
                auth()->logout();
                $this->error('Your account is not approved yet. Please wait for admin approval.');
            }
        } else {
            // Error message when credentials are not found in the database
            $this->error('The provided credentials do not match our records.');
        }
    }
}; ?>

<div
    class="min-h-screen flex items-center justify-center bg-gradient-to-tr from-[#0f2027] via-[#2c5364] to-[#009f51] p-6">
    <div
        class="relative bg-white/20 border border-white/30 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-lg px-10 py-12 overflow-hidden">
        <!-- Decorative Gradient Circle -->
        <div
            class="absolute -top-16 -right-16 w-48 h-48 bg-gradient-to-br from-green-400 via-green-200 to-white opacity-30 rounded-full z-0">
        </div>
        <!-- Logo -->
        <div class="flex justify-center mb-6 z-10 relative">
            <img src="{{ asset('logo.png') }}" alt="Flyvaly Logo" class="w-44 drop-shadow-lg rounded-xl bg-white/70 p-2">
        </div>
        <!-- Title -->
        <h2
            class="text-white text-center text-2xl font-extrabold mb-6 tracking-widest italic drop-shadow-lg z-10 relative">
            Partner Portal
        </h2>
        <!-- Login Form -->
        <x-form wire:submit="login" class="z-10 relative">
            <x-input wire:model="email" placeholder="Email Address" icon="o-envelope"
                class="w-full bg-white rounded-xl shadow-md custome-input-field py-5" />

            <x-password wire:model="password" type="password" icon="o-lock-closed" placeholder="Password"
                class="w-full bg-white rounded-xl shadow-md custome-input-field py-5" right />

            <div class="flex justify-between items-center text-xs mb-4">
                <span></span>
                <a href="#" class="hover:underline font-semibold transition-colors duration-200">Forgot
                    Password?</a>
            </div>

            <x-slot:actions>
                <div class="flex justify-between gap-3">
                    <x-button label="Home" class="btn-sm bg-white/80 text-green-700 hover:bg-green-50 transition"
                        link="/" />
                    <x-button label="Login" type="submit" icon="o-paper-airplane"
                        class="btn-primary btn-sm bg-gradient-to-r from-green-500 to-green-700 text-white shadow-lg hover:from-green-600 hover:to-green-800 transition"
                        spinner="login" />
                </div>
            </x-slot:actions>
        </x-form>
        <!-- Divider -->
        <div class="flex items-center my-6">
            <div class="flex-grow border-t border-white/30"></div>
            <span class="mx-4 text-xs text-white/70">or</span>
            <div class="flex-grow border-t border-white/30"></div>
        </div>
        <!-- Registration Link -->
        <p class="text-sm text-white/80 text-center">
            Don't have an account?
            <a href="/partner/register"
                class="underline font-semibold hover:text-green-600 text-green-500 transition">Register
                here</a>
        </p>
        <!-- Decorative Bottom Shape -->
        <div
            class="absolute -bottom-16 -left-16 w-48 h-48 bg-gradient-to-tl from-green-400 via-green-200 to-white opacity-20 rounded-full z-0">
        </div>
    </div>
</div>
