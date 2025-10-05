<?php

use App\Models\User;
use App\Enum\UserType;
use App\Enum\AgentStatus;
use App\Enum\UserStatus;
use Mary\Traits\Toast;
use App\Models\Customer;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

new #[Layout('components.layouts.frontendlogin')] #[Title('Sign In')] class extends Component {
    use Toast;

    public $name;
    public $email;
    public $password;
    public $confirmation_password;

    public function login()
    {
        $credentials = $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (auth()->attempt($credentials)) {
            $user = auth()->user();

            // Check if user is active and agent status is not pending
            if ($user->status == UserStatus::Active && optional($user->agent)->status !== AgentStatus::Pending) {
                request()->session()->regenerate();
                $this->success('Login Successful.', redirectTo: '/');
            } else {
                // Handle the error when status is not active or agent status is pending
                $this->error('The provided credentials do not match our records.');
            }
        } else {
            $this->error('The provided credentials do not match our records.');
        }
    }

    /**
     * Store a new agent and related data.
     *
     * @return void
     */
    public function register()
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'confirmation_password' => 'required|same:password',
        ]);

        DB::beginTransaction();
        try {
            // Create a new user for the agent
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'type' => UserType::Customer,
            ]);

            // Create associated customer record linked to the user
            Customer::create([
                'user_id' => $user->id,
            ]);

            Auth::login($user);

            DB::commit();
            $this->success('Registered & Logged in successfully', redirectTo: '/');
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div x-data="{ tab: 'login' }" class="flex items-center justify-center min-h-screen bg-gray-100">
    <x-card class="w-full max-w-lg my-10 p-8 bg-white shadow-xl border border-gray-200 rounded-2xl mx-3"
        style="border-radius: 16px; min-height: 380px;">

        <div class="flex flex-col items-center mb-8">
            <img src="{{ $globalSettings->logo_link ?? '/logo.png' }}" alt="Logo" class="w-48 mb-2"> <!-- Larger logo -->
            <div class="flex space-x-2 mt-4">
                <button class="px-6 py-2 rounded-full text-sm font-semibold transition-all duration-200"
                    :class="tab === 'login' ? 'bg-green-600 text-white shadow' :
                        'bg-green-100 text-green-700 hover:bg-green-200'"
                    @click="tab = 'login'">
                    Login
                </button>
                <button class="px-6 py-2 rounded-full text-sm font-semibold transition-all duration-200"
                    :class="tab === 'register' ? 'bg-green-600 text-white shadow' :
                        'bg-green-100 text-green-700 hover:bg-green-200'"
                    @click="tab = 'register'">
                    Register
                </button>
            </div>
        </div>

        <!-- Login Form -->
        <div x-show="tab === 'login'" x-cloak>
            <h1 class="text-base text-center mb-6 text-gray-800 font-semibold tracking-tight">Sign in to your account
            </h1>
            <x-form wire:submit="login" class="flex flex-col gap-y-4">
                <div class="relative">
                    <input type="email" placeholder="Email address"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2 pr-10 focus:outline-none" wire:model="email" required>
                    @error('email')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                    <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </span>
                </div>
                <div x-data="{ show: false }" class="relative">
                    <input :type="show ? 'text' : 'password'" placeholder="Password"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2 pr-10 focus:outline-none" wire:model="password" required>
                    @error('password')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                    <button type="button" class="absolute right-3 top-3 text-gray-400 focus:outline-none" @click="show = !show" tabindex="-1">
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm7.5 0a9.003 9.003 0 01-17.978 0A9.003 9.003 0 0121.5 12z" />
                        </svg>
                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-5.523 0-10-4.477-10-10 0-1.657.403-3.221 1.125-4.575m1.875 1.875A9.978 9.978 0 0121.5 12c0 1.657-.403 3.221-1.125 4.575m-1.875-1.875A9.978 9.978 0 012.5 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex justify-between items-center">
                    <a href="{{ route('forgetpassword') }}" class="text-sm font-semibold text-blue-500 transition duration-200 hover:underline">Forgot
                        password?</a>
                </div>
                <x-button type="submit"
                    class="w-full py-2 rounded-xl bg-gradient-to-r from-green-500 to-green-700 text-white shadow-lg hover:from-green-600 hover:to-green-800 transition-all duration-200"
                    label="Login" spinner="login" />
            </x-form>

            <div class="flex items-center my-6">
                <div class="flex-grow border-t border-gray-200"></div>
                <span class="mx-4 text-gray-400 text-sm">or</span>
                <div class="flex-grow border-t border-gray-200"></div>
            </div>

            <div class="flex flex-row gap-2 justify-center mt-4">
                <a href="{{ route('social.redirect', ['provider' => 'facebook', 'type' => 'customer']) }}"
                    class="group flex items-center justify-center w-14 h-14 rounded-full border-2 border-blue-100 bg-white text-blue-700 shadow-lg hover:bg-blue-50 hover:scale-105 transition-all duration-200 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 488 512"
                        class="transition-transform duration-200 group-hover:scale-110" fill="#1877F2">
                        <path
                            d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64h98.2V334.2H109.4V256h52.8V222.3c0-87.1 39.4-127.5 125-127.5c16.2 0 44.2 3.2 55.7 6.4V172c-6-.6-16.5-1-29.6-1c-42 0-58.2 15.9-58.2 57.2V256h83.6l-14.4 78.2H255V480H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64z" />
                    </svg>
                    <span class="sr-only">Login with Facebook</span>
                </a>
                <a href="{{ route('social.redirect', ['provider' => 'google', 'type' => 'customer']) }}"
                    class="group flex items-center justify-center w-14 h-14 rounded-full border-2 border-red-100 bg-white text-red-700 shadow-lg hover:bg-red-50 hover:scale-105 transition-all duration-200 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 18 18"
                        class="transition-transform duration-200 group-hover:scale-110">
                        <g fill="none" fill-rule="nonzero">
                            <path fill="#FFC107"
                                d="M17.825 7.237H17.1V7.2H9v3.6h5.086A5.398 5.398 0 0 1 3.6 9 5.4 5.4 0 0 1 9 3.6c1.377 0 2.629.52 3.582 1.368l2.546-2.546A8.958 8.958 0 0 0 9 0a9 9 0 1 0 8.825 7.237z">
                            </path>
                            <path fill="#FF3D00"
                                d="M1.038 4.811l2.957 2.168A5.398 5.398 0 0 1 9 3.6c1.377 0 2.629.52 3.582 1.368l2.546-2.546A8.958 8.958 0 0 0 9 0a8.995 8.995 0 0 0-7.962 4.811z">
                            </path>
                            <path fill="#4CAF50"
                                d="M9 18c2.325 0 4.437-.89 6.034-2.336l-2.785-2.357A5.36 5.36 0 0 1 9 14.4a5.397 5.397 0 0 1-5.077-3.576L.988 13.086A8.993 8.993 0 0 0 9 18z">
                            </path>
                            <path fill="#1976D2"
                                d="M17.825 7.237H17.1V7.2H9v3.6h5.086a5.418 5.418 0 0 1-1.839 2.507h.002l2.785 2.356C14.837 15.843 18 13.5 18 9c0-.603-.062-1.192-.175-1.763z">
                            </path>
                        </g>
                    </svg>
                    <span class="sr-only">Login with Google</span>
                </a>
            </div>
        </div>

        <!-- Register Form -->
        <div x-show="tab === 'register'" x-cloak>
            <h1 class="text-base text-center mb-6 text-gray-800 font-semibold tracking-tight">Create your account</h1>
            <x-form wire:submit="register" class="flex flex-col gap-y-3">
                <div class="relative">
                    <label for="register_name" class="block text-sm font-semibold text-green-700 mb-1">Full
                        Name</label>
                    <input id="register_name" type="text" placeholder="Full Name" wire:model="name"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2 focus:outline-none" required>
                    @error('name')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                    <span class="absolute right-3 top-9 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5.121 17.804A13.937 13.937 0 0112 15c2.485 0 4.797.657 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </span>
                </div>
                <div class="relative">
                    <label for="register_email" class="block text-sm font-semibold text-green-700 mb-1">Email
                        Address</label>
                    <input id="register_email" type="email" placeholder="Email address"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2 focus:outline-none" wire:model="email" required>
                    @error('email')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                    <span class="absolute right-3 top-9 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </span>
                </div>
                <div x-data="{ show: false }" class="relative">
                    <label for="register_password" class="block text-sm font-semibold text-green-700 mb-1">Password</label>
                    <input id="register_password" :type="show ? 'text' : 'password'" placeholder="Password"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2 pr-10 focus:outline-none" wire:model="password" required>
                    @error('password')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                    <button type="button" class="absolute right-3 top-9 text-gray-400 focus:outline-none" @click="show = !show" tabindex="-1">
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm7.5 0a9.003 9.003 0 01-17.978 0A9.003 9.003 0 0121.5 12z" />
                        </svg>
                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-5.523 0-10-4.477-10-10 0-1.657.403-3.221 1.125-4.575m1.875 1.875A9.978 9.978 0 0121.5 12c0 1.657-.403 3.221-1.125 4.575m-1.875-1.875A9.978 9.978 0 012.5 12" />
                        </svg>
                    </button>
                </div>
                <div x-data="{ show: false }" class="relative">
                    <label for="register_confirmation_password" class="block text-sm font-semibold text-green-700 mb-1">Confirm Password</label>
                    <input id="register_confirmation_password" :type="show ? 'text' : 'password'" placeholder="Confirm Password"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2 pr-10 focus:outline-none" wire:model="confirmation_password"
                        required>
                    @error('confirmation_password')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                    <button type="button" class="absolute right-3 top-9 text-gray-400 focus:outline-none" @click="show = !show" tabindex="-1">
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm7.5 0a9.003 9.003 0 01-17.978 0A9.003 9.003 0 0121.5 12z" />
                        </svg>
                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-5.523 0-10-4.477-10-10 0-1.657.403-3.221 1.125-4.575m1.875 1.875A9.978 9.978 0 0121.5 12c0 1.657-.403 3.221-1.125 4.575m-1.875-1.875A9.978 9.978 0 012.5 12" />
                        </svg>
                    </button>
                </div>
                <x-button type="submit"
                    class="w-full py-2 rounded-xl bg-gradient-to-r from-green-500 to-green-700 text-white shadow-lg hover:from-green-600 hover:to-green-800 transition-all duration-200"
                    label="Register" spinner="agentRegister" />
            </x-form>
        </div>
    </x-card>
</div>
