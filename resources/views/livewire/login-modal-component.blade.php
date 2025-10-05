<div>
    <div x-data="{ tab: 'login' }" class="flex items-center justify-center bg-gray-100">
        <x-modal wire:model="show" boxClass="max-w-md">
            <x-card>
                <img src="{{ $globalSettings->logo_link ?? '/logo.png' }}" alt="Logo" class="w-8/12 mx-auto">
                <div x-show="tab === 'login'" x-cloak>
                    <h2 class="text-sm md:text-md text-center mb-4 text-gray-700 font-semibold">Logged in to stay in
                        touch
                    </h2>
                    <x-form wire:submit="login" class="flex flex-col gap-y-4">
                        <input type="email" placeholder="customer@gmail.com" class="border-b pb-1 outline-none" wire:model="email" required>
                        <div x-data="{ show: false }" class="flex items-center gap-x-1 border-b pb-1">
                            <input :type="show ? 'text' : 'password'" placeholder="Enter Your Password" class="outline-none flex-grow"
                                wire:model="password" required>
                            <svg x-show="!show" @click="show = true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-4 cursor-pointer">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                            <svg x-show="show" @click="show = false" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-4 cursor-pointer">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </div>
                        <a href="{{ route('forgetpassword') }}" class="text-sm text-blue-500 hover:underline font-semibold">Forgot
                            password?</a>
                        <button type="submit" class="btn-primary w-32 mx-auto">Login</button>
                    </x-form>
                </div>
                <!-- Register Form -->
                <div x-show="tab === 'register'" x-cloak>
                    <h1 class="text-center mb-4 text-gray-700 font-bold">Create a New Account</h1>
                    <x-form wire:submit="register" class="flex flex-col gap-y-4">
                        <input type="text" placeholder="Enter Your Name" wire:model="name" class="border-b pb-1 outline-none" required>
                        <input type="email" placeholder="Enter Your Email Address" class="border-b pb-1 outline-none" wire:model="email" required>
                        <div x-data="{ show: false }" class="flex items-center gap-x-1 border-b pb-1">
                            <input :type="show ? 'text' : 'password'" placeholder="Enter Your Password" class="outline-none flex-grow"
                                wire:model="password" required>
                            <svg x-show="!show" @click="show = true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-4 cursor-pointer">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                            <svg x-show="show" @click="show = false" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-4 cursor-pointer">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </div>
                        <div x-data="{ show: false }" class="flex items-center gap-x-1 border-b pb-1">
                            <input :type="show ? 'text' : 'password'" placeholder="Confirm Password" class="outline-none flex-grow"
                                wire:model="confirmation_password" required>
                            <svg x-show="!show" @click="show = true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-4 cursor-pointer">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                            <svg x-show="show" @click="show = false" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-4 cursor-pointer">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </div>
                        <button type="submit" class="btn-primary">Register</button>
                    </x-form>
                </div>

                <!-- Footer -->
                <p class="text-center text-sm text-gray-600 mt-6">
                    <span x-show="tab === 'login'">Don't have an account? <a href="#"
                            class="text-green-500 hover:text-green-600 hover:underline italic" @click.prevent="tab = 'register'">Register
                            Now</a></span>
                    <span x-show="tab === 'register'">Already have an account? <a href="#"
                            class="text-green-500 hover:text-green-600 hover:underline italic" @click.prevent="tab = 'login'">Login Here</a></span>
                </p>

                <a href="{{ route('social.redirect', ['provider' => 'facebook', 'type' => 'customer']) }}"
                    class="text-center bg-[#1877F2] text-white w-full mt-4 p-2 rounded-lg shadow-lg hover:bg-[#1558A2] transition duration-300 transform hover:scale-105 flex items-center justify-center gap-x-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 488 512" fill="#ffffff"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64h98.2V334.2H109.4V256h52.8V222.3c0-87.1 39.4-127.5 125-127.5c16.2 0 44.2 3.2 55.7 6.4V172c-6-.6-16.5-1-29.6-1c-42 0-58.2 15.9-58.2 57.2V256h83.6l-14.4 78.2H255V480H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64z" />
                    </svg>
                    <span class="text-md font-medium">Login with Facebook</span>
                </a>

                <a href="{{ route('social.redirect', ['provider' => 'google', 'type' => 'customer']) }}"
                    class="text-center bg-white border border-[#DADCE0] text-[#3C4043] w-full mt-2 p-2 rounded-lg shadow-lg hover:bg-[#F0F0F0] transition duration-300 transform hover:scale-105 flex items-center justify-center gap-x-3">
                    <svg data-v-67f23e14="" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 18 18">
                        <g data-v-67f23e14="" fill="none" fill-rule="nonzero">
                            <path data-v-67f23e14="" fill="#FFC107"
                                d="M17.825 7.237H17.1V7.2H9v3.6h5.086A5.398 5.398 0 0 1 3.6 9 5.4 5.4 0 0 1 9 3.6c1.377 0 2.629.52 3.582 1.368l2.546-2.546A8.958 8.958 0 0 0 9 0a9 9 0 1 0 8.825 7.237z">
                            </path>
                            <path data-v-67f23e14="" fill="#FF3D00"
                                d="M1.038 4.811l2.957 2.168A5.398 5.398 0 0 1 9 3.6c1.377 0 2.629.52 3.582 1.368l2.546-2.546A8.958 8.958 0 0 0 9 0a8.995 8.995 0 0 0-7.962 4.811z">
                            </path>
                            <path data-v-67f23e14="" fill="#4CAF50"
                                d="M9 18c2.325 0 4.437-.89 6.034-2.336l-2.785-2.357A5.36 5.36 0 0 1 9 14.4a5.397 5.397 0 0 1-5.077-3.576L.988 13.086A8.993 8.993 0 0 0 9 18z">
                            </path>
                            <path data-v-67f23e14="" fill="#1976D2"
                                d="M17.825 7.237H17.1V7.2H9v3.6h5.086a5.418 5.418 0 0 1-1.839 2.507h.002l2.785 2.356C14.837 15.843 18 13.5 18 9c0-.603-.062-1.192-.175-1.763z">
                            </path>
                        </g>
                    </svg>
                    <span class="text-md font-medium">Login with Google</span>
                </a>
            </x-card>
        </x-modal>

    </div>
</div>
