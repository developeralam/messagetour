<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\AboutUs;

new #[Layout('components.layouts.app')] #[Title('About Us')] class extends Component {
    public $description;

    public function mount()
    {
        $data = AboutUs::first();
        if ($data) {
            $this->description = $data->description;
        }
    }
}; ?>

<div class="relative min-h-screen bg-gradient-to-br from-green-50 via-white to-green-100 overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-green-300 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-emerald-300 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-pulse"
            style="animation-delay: 2s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-teal-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-pulse"
            style="animation-delay: 4s;"></div>
    </div>

    <!-- Geometric Shapes -->
    <div class="absolute top-20 left-10 w-20 h-20 border-2 border-green-300 rotate-45 animate-spin" style="animation-duration: 20s;"></div>
    <div class="absolute bottom-20 right-10 w-16 h-16 bg-gradient-to-r from-green-400 to-emerald-400 rounded-full animate-bounce"
        style="animation-delay: 1s;"></div>
    <div class="absolute top-1/3 right-20 w-12 h-12 border-2 border-green-400 rotate-12 animate-pulse"></div>

    <!-- Travel Icons -->
    <div class="absolute top-32 right-32 text-green-300 opacity-20 animate-float">
        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
        </svg>
    </div>
    <div class="absolute bottom-32 left-32 text-green-400 opacity-20 animate-float" style="animation-delay: 3s;">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
        </svg>
    </div>

    <div class="relative z-10 px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-5xl mx-auto">
            <!-- Hero Section -->
            <div class="text-center mb-12">
                <div class="inline-block relative">
                    <h1
                        class="text-4xl sm:text-5xl lg:text-7xl font-black bg-gradient-to-r from-green-600 via-green-500 to-emerald-600 bg-clip-text text-transparent leading-tight">
                        About Us
                    </h1>
                    <div
                        class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 w-24 h-1 bg-gradient-to-r from-green-400 to-emerald-400 rounded-full">
                    </div>
                </div>
                <p class="mt-6 text-lg sm:text-xl text-gray-700 max-w-2xl mx-auto leading-relaxed">
                    Discover our story, mission, and the passion that drives us to create unforgettable travel experiences
                </p>
            </div>

            <!-- Main Content Card -->
            <div class="relative">
                <!-- Glass Morphism Card -->
                <div class="bg-white/90 backdrop-blur-sm border border-green-200 rounded-3xl shadow-2xl overflow-hidden">
                    <!-- Decorative Header -->
                    <div class="relative h-2 bg-gradient-to-r from-green-500 via-emerald-500 to-teal-500"></div>

                    <div class="p-6 sm:p-8 lg:p-12">
                        <!-- Content with Enhanced Typography -->
                        <div class="prose prose-lg max-w-none">
                            <div class="text-gray-700 leading-relaxed space-y-6 text-base sm:text-lg">
                                {!! $description !!}
                            </div>
                        </div>

                        <!-- Interactive Elements -->
                        <div class="mt-12 grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <!-- Feature Cards -->
                            <div
                                class="group relative p-6 bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl border border-green-200 hover:border-green-400 transition-all duration-300 hover:transform hover:scale-105 hover:shadow-lg">
                                <div
                                    class="absolute inset-0 bg-gradient-to-br from-green-100/50 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                </div>
                                <div class="relative z-10">
                                    <div
                                        class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl flex items-center justify-center mb-4">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                            </path>
                                        </svg>
                                    </div>
                                    <h3 class="text-green-800 font-semibold text-lg mb-2">Global Reach</h3>
                                    <p class="text-gray-600 text-sm">Connecting travelers to destinations worldwide with expert local knowledge</p>
                                </div>
                            </div>

                            <div
                                class="group relative p-6 bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl border border-green-200 hover:border-green-400 transition-all duration-300 hover:transform hover:scale-105 hover:shadow-lg">
                                <div
                                    class="absolute inset-0 bg-gradient-to-br from-green-100/50 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                </div>
                                <div class="relative z-10">
                                    <div
                                        class="w-12 h-12 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center mb-4">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                            </path>
                                        </svg>
                                    </div>
                                    <h3 class="text-green-800 font-semibold text-lg mb-2">Passionate Service</h3>
                                    <p class="text-gray-600 text-sm">Dedicated to creating memorable experiences that last a lifetime</p>
                                </div>
                            </div>

                            <div
                                class="group relative p-6 bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl border border-green-200 hover:border-green-400 transition-all duration-300 hover:transform hover:scale-105 hover:shadow-lg">
                                <div
                                    class="absolute inset-0 bg-gradient-to-br from-green-100/50 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                </div>
                                <div class="relative z-10">
                                    <div
                                        class="w-12 h-12 bg-gradient-to-r from-teal-500 to-green-500 rounded-xl flex items-center justify-center mb-4">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-green-800 font-semibold text-lg mb-2">Trusted Excellence</h3>
                                    <p class="text-gray-600 text-sm">Committed to delivering the highest quality travel services and support</p>
                                </div>
                            </div>
                        </div>

                        <!-- Call to Action -->
                        <div class="mt-12 text-center">
                            <div class="inline-flex items-center space-x-4 bg-green-50 px-6 py-3 rounded-full border border-green-200">
                                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                <span class="text-green-700 text-sm font-medium">Ready to start your journey with us?</span>
                                <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse" style="animation-delay: 1s;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Floating Elements -->
                <div class="absolute -top-4 -right-4 w-8 h-8 bg-gradient-to-r from-green-400 to-emerald-400 rounded-full animate-ping opacity-75">
                </div>
                <div class="absolute -bottom-4 -left-4 w-6 h-6 bg-gradient-to-r from-emerald-400 to-teal-400 rounded-full animate-ping opacity-75"
                    style="animation-delay: 2s;"></div>
            </div>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        .prose h1,
        .prose h2,
        .prose h3,
        .prose h4,
        .prose h5,
        .prose h6 {
            color: #065f46;
        }

        .prose p {
            color: #374151;
        }

        .prose strong {
            color: #047857;
        }

        .prose a {
            color: #059669;
        }

        .prose a:hover {
            color: #047857;
        }
    </style>
</div>
