<?php

use App\Models\User;
use App\Models\Agent;
use App\Enum\UserType;
use Mary\Traits\Toast;
use App\Enum\AgentType;
use App\Enum\AgentStatus;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PartnarRegisterNotification;

new #[Layout('components.layouts.frontendlogin')] #[Title('Partner Register')] class extends Component {
    use Toast;

    #[Rule('required')]
    public $name;

    #[Rule('required|email')]
    public $email;

    #[Rule('required')]
    public $password;

    #[Rule('required')]
    public $agent_type;

    #[Rule('required|same:password')]
    public $confirmation_password;

    public $businessTypes = [];

    public function mount()
    {
        // Initialize business types if needed
        $this->businessTypes = AgentType::getTypes();
    }

    /**
     * Store a new agent and related data.
     *
     * @return void
     */
    public function agentRegister()
    {
        $this->validate();
        DB::beginTransaction();
        try {
            // Create a new user for the agent
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'type' => UserType::Agent,
            ]);

            // Create associated agent record linked to the user
            $agent = Agent::create([
                'user_id' => $user->id,
                'agent_type' => $this->agent_type,
                'status' => AgentStatus::Pending,
            ]);

            // Send notifications
            $admin = User::where('type', UserType::Admin)->get();

            // Notify all admin
            Notification::send($admin, new PartnarRegisterNotification($agent));

            DB::commit();
            $this->success('Agent Added Successfully. Please wait for admin approval', redirectTo: '/');
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div
    class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-100 via-white to-green-200 py-10 px-2">
    <div
        class="w-full max-w-xl bg-white/95 backdrop-blur-2xl shadow-2xl rounded-3xl p-8 relative overflow-hidden border border-green-100">
        <!-- Decorative Gradient Blobs -->
        <div
            class="absolute -top-16 -left-16 w-40 h-40 bg-gradient-to-tr from-green-400 via-green-300 to-green-500 opacity-30 rounded-full z-0 blur-2xl">
        </div>
        <div
            class="absolute -bottom-16 -right-16 w-40 h-40 bg-gradient-to-tr from-green-400 via-green-300 to-green-500 opacity-20 rounded-full z-0 blur-2xl">
        </div>
        <div class="relative z-10">
            <div class="flex flex-col items-center mb-7">
                <div class="mb-2">
                    <img src="{{ asset('logo.png') }}" alt="Logo" class="w-full h-20 object-contain">
                </div>
                <h1 class="text-2xl font-extrabold text-green-800 mb-1 tracking-tight">Become a Partner</h1>
                <p class="text-gray-500 text-sm text-center max-w-xs">Unlock exclusive benefits, priority support, and
                    business growth opportunities with our premium partner program.</p>
            </div>
            <x-form x-data="{ show: false, showConfirm: false }" wire:submit="agentRegister">
                <div>
                    <label class="block text-green-700 text-sm font-semibold mb-1" for="name">Full Name</label>
                    <div class="relative">
                        <input id="name" type="text" placeholder="Enter your name"
                            class="w-full px-4 py-2 border border-gray-400 rounded-xl focus:outline-none transition shadow-sm"
                            wire:model="name" required>
                        <span class="absolute right-3 top-2.5 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5.121 17.804A13.937 13.937 0 0112 15c2.485 0 4.797.657 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div>
                    <label class="block text-green-700 text-sm font-semibold mb-1" for="email">Email Address</label>
                    <div class="relative">
                        <input id="email" type="email" placeholder="Enter your email"
                            class="w-full px-4 py-2 border border-gray-400 rounded-xl focus:outline-none transition shadow-sm"
                            wire:model="email" required>
                        <span class="absolute right-3 top-2.5 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div>
                    <label class="block text-green-700 text-sm font-semibold mb-1" for="password">Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" id="password" placeholder="Create a password"
                            class="w-full px-4 py-2 border border-gray-400 rounded-xl focus:outline-none transition shadow-sm"
                            wire:model="password" required>
                        <button type="button" class="absolute right-3 top-2 text-gray-400 focus:outline-none"
                            @click="show = !show" tabindex="-1">
                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm7.5 0a9.003 9.003 0 01-17.978 0A9.003 9.003 0 0121.5 12z" />
                            </svg>
                            <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-5.523 0-10-4.477-10-10 0-1.657.403-3.221 1.125-4.575m1.875 1.875A9.978 9.978 0 0121.5 12c0 1.657-.403 3.221-1.125 4.575m-1.875-1.875A9.978 9.978 0 012.5 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-green-700 text-sm font-semibold mb-1" for="confirmation_password">Confirm
                        Password</label>
                    <div class="relative">
                        <input :type="showConfirm ? 'text' : 'password'" id="confirmation_password"
                            placeholder="Confirm your password"
                            class="w-full px-4 py-2 border border-gray-400 rounded-xl focus:outline-none transition shadow-sm"
                            wire:model="confirmation_password" required>
                        <button type="button" class="absolute right-3 top-2 text-gray-400 focus:outline-none"
                            @click="showConfirm = !showConfirm" tabindex="-1">
                            <svg x-show="!showConfirm" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm7.5 0a9.003 9.003 0 01-17.978 0A9.003 9.003 0 0121.5 12z" />
                            </svg>
                            <svg x-show="showConfirm" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-5.523 0-10-4.477-10-10 0-1.657.403-3.221 1.125-4.575m1.875 1.875A9.978 9.978 0 0121.5 12c0 1.657-.403 3.221-1.125 4.575m-1.875-1.875A9.978 9.978 0 012.5 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-green-700 text-sm font-semibold mb-1" for="agent_type">Business
                        Type</label>
                    <x-choices class="custom-select-field w-full" wire:model="agent_type" :options="$businessTypes"
                        placeholder="Select Business Type" single />
                </div>
                <x-button type="submit"
                    class="w-full py-2.5 rounded-xl bg-gradient-to-r from-green-500 to-green-700 text-white shadow-lg hover:from-green-600 hover:to-green-800 transition-all duration-200"
                    label="Register" spinner="agentRegister" />

            </x-form>
            <div class="mt-6 text-center">
                <p class="text-gray-600 text-sm">
                    Already a partner?
                    <a href="/partner/login" class="text-green-600 hover:underline font-semibold">Sign In</a>
                </p>
            </div>
        </div>
    </div>
</div>
