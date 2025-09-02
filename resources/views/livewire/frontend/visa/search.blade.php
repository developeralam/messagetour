<?php

use App\Models\Visa;
use App\Enum\VisaType;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Session;

new #[Layout('components.layouts.service-details')] #[Title('Visa List')] class extends Component {
    use WithPagination, Toast;

    public $visa_types = [];
    public $visa_title;
    public $origin;
    public $destination;
    public $type;
    public $visa_type;
    public $selectedTab;

    protected $queryString = [
        'origin' => ['except' => ''],
        'destination' => ['except' => ''],
        'type' => ['except' => ''],
        'visa_type' => ['except' => ''],
    ];

    public function mount()
    {
        // Get parameters from URL
        $this->selectedTab = request()->query('selectedTab', 'visa'); // default 'visa'

        $this->origin = request()->query('origin', '');
        $this->destination = request()->query('destination', '');
        $this->type = request()->query('type', '');
        $this->visa_types = VisaType::getVisaTypes();
    }

    public function resetFilters()
    {
        // Reset the filter properties to their default values
        $this->origin = ''; // Reset the origin
        $this->destination = ''; // Reset the destination
        $this->visa_type = ''; // Reset the visa_type
        $this->type = ''; // Reset the type
    }

    public function with()
    {
        // Start the Visa query
        $query = Visa::query()->with(['origin', 'destination']);

        // Apply filters if parameters are set
        if ($this->origin) {
            $query->where('origin_country', $this->origin); // Filter by origin country
        }

        if ($this->destination) {
            $query->where('destination_country', $this->destination); // Filter by destination country
        }

        if (!empty($this->visa_title)) {
            $query->where('title', 'like', '%' . $this->visa_title . '%');
        }

        if ($this->visa_type) {
            $query->where('type', $this->visa_type); // Filter by visa visa_type
        }

        // Get the count of the visas
        $totalVisas = $query->count();

        // Paginate the results (limit 12 per page)
        $paginatedVisas = $query->latest()->paginate(12)->withQueryString();

        return [
            'visas' => $paginatedVisas,
            'totalVisas' => $totalVisas, // Pass the total count to the view
        ];
    }
}; ?>

<div class="bg-gray-100">
    <section
        class="relative items-center bg-[url('https://flyvaly.com/assets/images/bg/home-bg.png')] bg-no-repeat bg-cover bg-center py-20 z-10">
        <div class="absolute inset-0 bg-slate-900/40"></div>
        <div class="relative py-12 md:py-20">
            <div class="max-w-6xl mx-auto">
                <livewire:home-search-component />
            </div>
            <!--end container-->
        </div>
    </section>
    <section class="mt-16">
        <div class="max-w-6xl mx-auto px-3 md:px-0">

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mt-6 pb-6 items-start">
                <div class="col-span-1 md:col-span-3 border p-3 rounded-md shadow-md bg-white">
                    <div class="flex justify-between my-4">
                        <span class="font-semibold text-md">Filter By</span>
                        <x-button class="btn-primary custom-reset-button btn-sm" label="RESET"
                            wire:click="resetFilters" />
                    </div>
                    <!-- Property Name Search Input -->
                    <div class="mb-4 border-t-2 border-green-400 py-2">
                        <label for="visa_title" class="block text-sm font-semibold mb-1">Search Visa</label>
                        <div class="relative flex items-center">
                            <input type="text" id="visa_title" placeholder="Search By Visa Name"
                                wire:model.live="visa_title"
                                class="w-full px-3 py-2 border rounded-md focus:outline-none text-sm" />
                        </div>
                    </div>
                    <div class="border-t-2 border-green-400"></div>
                    <div class="my-3">
                        <h3 class="border-b-2 border-green-400 pb-3 mb-2 font-semibold">Visa Type</h3>
                        @foreach ($visa_types as $type)
                            <div class="mb-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" id="visa_type_{{ $type['id'] }}" class="custom-radio-dot"
                                        wire:model.live="visa_type" value="{{ $type['id'] }}" />
                                    <span class="text-sm font-medium">{{ $type['name'] }}</span>
                                </label>
                            </div>
                        @endforeach

                    </div>
                </div>
                <div class="col-span-1 md:col-span-9 grid grid-cols-1 md:grid-cols-3 gap-3">
                    @if ($visas->count() > 0)
                        @foreach ($visas as $visa)
                            <div class="col-span-1 rounded-lg shadow-md border p-4 bg-white">

                                <a class="cursor-pointer text-lg font-medium text-gray-800 hover:text-green-600 hover:underline"
                                    wire:navigate
                                    href="{{ route('frontend.visa.details', [
                                        'slug' => $visa->slug,
                                        'origin' => request()->query('origin'),
                                        'destination' => request()->query('destination'),
                                        'type' => request()->query('type'),
                                    ]) }}">
                                    {!! \Illuminate\Support\Str::limit(
                                        $visa->title ?? '',
                                        25,
                                        ' <span class="text-blue-500 cursor-pointer">..</span>',
                                    ) !!}
                                </a>

                                <div class="flex items-center gap-2">
                                    <p class="font-semibold text-gray-800 text-sm">Visa Type:</p>
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $visa->type->name }}
                                    </span>
                                </div>

                                <div class="mt-1 mb-2">
                                    <div class="flex items-center space-x-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500 mt-2"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 9L21 3m-7 6L3 12" />
                                        </svg>
                                        <p class="text-sm">Origin:
                                            <span
                                                class="text-orange-500 font-semibold">{{ $visa->origin->name ?? '' }}</span>
                                        </p>
                                    </div>
                                </div>

                                <div class="my-2">
                                    <div class="flex items-center space-x-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                        <p class="text-sm">Destination:
                                            <span
                                                class="text-blue-600 font-semibold">{{ $visa->destination->name ?? '' }}</span>
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="flex items-start text-xs text-orange-500 bg-orange-100 px-3 py-2 rounded my-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 flex-shrink-0"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                                    </svg>
                                    <p>Please contact us for document processing.</p>
                                </div>

                                <a wire:navigate
                                    href="{{ route('frontend.visa.details', [
                                        'slug' => $visa->slug,
                                        'origin' => request()->query('origin'),
                                        'destination' => request()->query('destination'),
                                        'type' => request()->query('type'),
                                        'selectedTab' => $selectedTab,
                                    ]) }}"
                                    class="bg-green-500 hover:bg-green-600 text-white font-semibold py-1 w-full rounded text-sm block text-center">
                                    SELECT OFFER
                                </a>
                            </div>
                        @endforeach
                    @endif

                    <div class="mary-table-pagination col-span-1 md:col-span-3">
                        {{-- <div class="border border-x-0 border-t-0 border-b-1 border-b-base-300 mb-5"></div> --}}
                        {{ $visas->onEachSide(1)->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
