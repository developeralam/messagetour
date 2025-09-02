<div>
    <x-modal wire:model="openModal" title="Custom Tour Request" boxClass="max-w-4xl">
        <x-form wire:submit="storeQuery">
            <div class="bg-gradient-to-br from-green-50 to-blue-50 rounded-xl p-6">
                <!-- Header Section -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                        <x-icon name="fas.route" class="w-8 h-8 text-green-600" />
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Create Your Custom Tour</h2>
                    <p class="text-gray-600">Tell us your requirements and we'll craft the perfect itinerary for you</p>
                </div>

                <!-- Progress Steps (Optional) -->
                <div class="flex items-center justify-center mb-8">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <div
                                class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-bold">
                                1</div>
                            <span class="ml-2 text-sm font-medium text-gray-700">Personal Info</span>
                        </div>
                        <div class="w-12 h-0.5 bg-green-200"></div>
                        <div class="flex items-center">
                            <div
                                class="w-8 h-8 bg-green-200 text-gray-500 rounded-full flex items-center justify-center text-sm font-bold">
                                2</div>
                            <span class="ml-2 text-sm font-medium text-gray-500">Travel Details</span>
                        </div>
                        <div class="w-12 h-0.5 bg-green-200"></div>
                        <div class="flex items-center">
                            <div
                                class="w-8 h-8 bg-green-200 text-gray-500 rounded-full flex items-center justify-center text-sm font-bold">
                                3</div>
                            <span class="ml-2 text-sm font-medium text-gray-500">Submit</span>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="space-y-8">
                    <!-- Personal Information Section -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                <x-icon name="fas.user" class="w-5 h-5 text-blue-600" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Personal Information</h3>
                                <p class="text-sm text-gray-500">Tell us about yourself or your company</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Full Name</label>
                                <x-input wire:model="name" placeholder="Enter your full name" required
                                    class="w-full border-gray-200 focus:border-green-500 focus:ring-green-500 rounded-lg" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Email Address</label>
                                <x-input wire:model="email" placeholder="your.email@example.com" required
                                    class="w-full border-gray-200 focus:border-green-500 focus:ring-green-500 rounded-lg" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Phone Number</label>
                                <x-input wire:model="phone" placeholder="+880 1XXX XXX XXX" required
                                    class="w-full border-gray-200 focus:border-green-500 focus:ring-green-500 rounded-lg" />
                            </div>
                        </div>
                    </div>

                    <!-- Travel Details Section -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                <x-icon name="fas.plane" class="w-5 h-5 text-green-600" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Travel Details</h3>
                                <p class="text-sm text-gray-500">Where and when do you want to travel?</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Destination</label>
                                <x-choices wire:model.live="destination_country" placeholder="Select your destination"
                                    :options="$destinationCountries" single required search-function="countrySearch" searchable
                                    class="w-full border-gray-200 focus:border-green-500 focus:ring-green-500 rounded-lg" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Travel Date</label>
                                <x-datepicker wire:model="travel_date" icon="o-calendar" :config="$config2"
                                    class="w-full border-gray-200 focus:border-green-500 focus:ring-green-500 rounded-lg"
                                    required />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Program Type</label>
                                <x-input wire:model="program" placeholder="e.g., Business, Leisure, Adventure" required
                                    class="w-full border-gray-200 focus:border-green-500 focus:ring-green-500 rounded-lg" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Hotel Category</label>
                                <x-choices wire:model="hotel_type" placeholder="Select hotel type" :options="$hotelTypes"
                                    single required
                                    class="w-full border-gray-200 focus:border-green-500 focus:ring-green-500 rounded-lg" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Room Type</label>
                                <x-choices wire:model="hotel_room_type" placeholder="Select room type" :options="$hotelRoomTypes"
                                    single
                                    class="w-full border-gray-200 focus:border-green-500 focus:ring-green-500 rounded-lg" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Meal Plan</label>
                                <x-input wire:model="meals" placeholder="e.g., Breakfast, Half Board, Full Board"
                                    required
                                    class="w-full border-gray-200 focus:border-green-500 focus:ring-green-500 rounded-lg" />
                            </div>
                        </div>
                    </div>

                    <!-- Additional Preferences Section -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                                <x-icon name="fas.heart" class="w-5 h-5 text-purple-600" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Additional Preferences</h3>
                                <p class="text-sm text-gray-500">Help us customize your perfect experience</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Group Size</label>
                                <x-textarea wire:model="group_size" placeholder="Number of travelers" rows="2"
                                    required
                                    class="w-full border-gray-200 focus:border-green-500 focus:ring-green-500 rounded-lg resize-none" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Meal Preferences</label>
                                <x-textarea wire:model="meals_choices" placeholder="Any dietary requirements?"
                                    rows="2"
                                    class="w-full border-gray-200 focus:border-green-500 focus:ring-green-500 rounded-lg resize-none" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Must-See Places</label>
                                <x-textarea wire:model="recommend_places" placeholder="Places you want to visit"
                                    rows="2"
                                    class="w-full border-gray-200 focus:border-green-500 focus:ring-green-500 rounded-lg resize-none" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Activities</label>
                                <x-textarea wire:model="activities" placeholder="Activities you're interested in"
                                    rows="2"
                                    class="w-full border-gray-200 focus:border-green-500 focus:ring-green-500 rounded-lg resize-none" />
                            </div>
                        </div>
                    </div>

                    <!-- Services Section -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-4">
                                <x-icon name="fas.cogs" class="w-5 h-5 text-orange-600" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Additional Services</h3>
                                <p class="text-sm text-gray-500">Select the services you need</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <label
                                class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:border-green-300 hover:bg-green-50 transition-all duration-200">
                                <x-checkbox wire:model="visa_service"
                                    class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500 custom-checkbox" />
                                <div class="ml-4">
                                    <div class="flex items-center">
                                        <x-icon name="fas.passport" class="w-5 h-5 text-green-600 mr-2" />
                                        <span class="font-medium text-gray-800">Visa Service</span>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">We'll handle your visa application</p>
                                </div>
                            </label>

                            <label
                                class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:border-green-300 hover:bg-green-50 transition-all duration-200">
                                <x-checkbox wire:model="air_ticket"
                                    class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500 custom-checkbox" />
                                <div class="ml-4">
                                    <div class="flex items-center">
                                        <x-icon name="fas.ticket" class="w-5 h-5 text-green-600 mr-2" />
                                        <span class="font-medium text-gray-800">Air Tickets</span>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">Flight booking and management</p>
                                </div>
                            </label>

                            <label
                                class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:border-green-300 hover:bg-green-50 transition-all duration-200">
                                <x-checkbox wire:model="tour_guide"
                                    class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500 custom-checkbox" />
                                <div class="ml-4">
                                    <div class="flex items-center">
                                        <x-icon name="fas.user-tie" class="w-5 h-5 text-green-600 mr-2" />
                                        <span class="font-medium text-gray-800">Tour Guide</span>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">Professional guide service</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div
                    class="flex flex-col sm:flex-row justify-between items-center gap-4 mt-8 pt-6 border-t border-gray-200">

                    <div class="flex gap-3">
                        <button type="button" wire:click="$set('openModal', false)"
                            class="px-6 py-3 text-gray-600 bg-gray-100 hover:bg-gray-200 font-medium rounded-lg transition-all duration-200">
                            Cancel
                        </button>
                        <x-button type="submit" label="Submit Request"
                            class="bg-green-500 hover:bg-green-600 text-white font-semibold px-8 py-3 rounded-lg shadow-lg transition-all duration-200 transform hover:scale-105"
                            spinner="storeQuery" />
                    </div>
                </div>
            </div>
        </x-form>
    </x-modal>
</div>
