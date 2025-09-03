<?php

use App\Models\Visa;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Enum\GenderType;
use App\Models\District;
use App\Models\Division;
use App\Enum\VisaPurpose;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Traits\InteractsWithImageUploads;
use Illuminate\Contracts\Database\Eloquent\Builder;

new #[Layout('components.layouts.service-details')] #[Title('Visa Details')] class extends Component {
    use Toast, WithFileUploads, InteractsWithImageUploads;
    public $selected_fee;
    public $fee_per_person = 0;
    public $total_fee = 0;
    public $convenient_fee = 0;
    public $visa;
    public $origin;
    public $destination;
    public $type;
    public $selectedTab;
    public $visaPurposes = [];
    public $genders = [];
    public $countries = [];
    public $divisions = [];
    public $districts = [];
    public $uploadedFiles = [];

    // E-visa Booking perameter
    public $first_name;
    public $last_name;
    public $dob;
    public $gender;
    public $nationality;
    public $birth_country;
    public $nid;
    public $email;
    public $phone;
    //passport information
    public $passport_number;
    public $passport_issue_date;
    public $passport_exp_date;
    //present address
    public $pa_house_no;
    public $pa_address;
    public $pa_zip_code;
    public $pa_country;
    public $pa_division;
    public $pa_district;
    //travel information
    public $des_address;
    public $des_phone;
    public $des_post_code;
    public $arr_date;
    public $entry_port;
    public $purpose;
    //file upload
    public $med_appointment_doc;
    public $med_previous_doc;
    public $busi_invitation;
    public $busi_company_doc;
    public $busi_other_doc;
    public $fam_invitation;
    public $fam_rel_with_invitor;
    public $fam_invitor_passport;
    public $fam_invitor_other_doc;
    public $uni_admission_letter;
    public $uni_immi_approval;
    public $uni_other_doc;
    public $work_permit;
    public $work_recruiter;
    public $work_other_doc;
    public $passport_doc;
    public $bank_state_doc;
    public $bank_solvency_doc;
    public $busi_trade_license;
    public $busi_office_pad;
    public $busi_visiting_card;
    public $sholder_noc;
    public $sholder_visiting_card;
    public $sholder_pay_slip;
    public $stu_id_card;
    public $stu_last_certificate;
    public $govt_prof_doc;
    public $retired_doc;

    #[Rule('required')]
    public $total_travellers = 1;

    public $document_collection_date;

    public function mount($slug)
    {
        // Fetch visa details based on slug
        $this->visa = Visa::with('visaFees')->where('slug', $slug)->first();
        $this->selectedTab = request()->query('selectedTab', 'visa'); // default 'visa'

        $this->convenient_fee = $this->visa->convenient_fee;
        $this->origin = request()->query('origin', '');
        $this->destination = request()->query('destination', '');
        $this->type = request()->query('type', '');

        $this->visaPurposes = VisaPurpose::getVisaPurposes();
        $this->genders = GenderType::getGenderTypes();
        $this->countries = Country::select(['id', 'name'])->get();
        $this->divisions = Division::select(['id', 'name'])->get();
        $this->divisions = District::select(['id', 'name'])->get();
    }
    public function updatedSelectedFee()
    {
        if ($this->selected_fee) {
            foreach ($this->visa->visaFees as $fee) {
                if ($fee->id == $this->selected_fee) {
                    $this->fee_per_person = $fee->fee;
                    $this->updateTotalFee();
                    return;
                }
            }
        }
    }

    public function updatedTotalTravellers()
    {
        $this->updateTotalFee();
    }

    private function updateTotalFee()
    {
        $this->total_fee = $this->fee_per_person * $this->total_travellers;
    }
    public function proceedToBooking()
    {
        // Validate required fields manually
        if (!$this->selected_fee) {
            $this->error('Please select a visa fee before proceeding.');
            return;
        }

        if (!$this->document_collection_date) {
            $this->error('Please select document collection date before proceeding.');
            return;
        }

        // Prepare booking data for session storage
        $bookingData = [
            'total_travellers' => $this->total_travellers,
            'selected_fee' => $this->fee_per_person,
            'convenient_fee' => $this->convenient_fee,
            'document_collection_date' => $this->document_collection_date,
            'total_fee' => $this->total_fee,
        ];

        // Store data in session
        Session::put('booking_data', $bookingData);

        if (Auth::check()) {
            return redirect()->route('frontend.visa.booking', [
                'slug' => $this->visa->slug,
                'origin' => $this->origin,
                'destination' => $this->destination,
                'type' => $this->type,
            ]);
        } else {
            // User is not authenticated, dispatch event to show login modal
            $this->dispatch(
                'showLoginModal',
                route('frontend.visa.booking', [
                    'slug' => $this->visa->slug,
                    'origin' => $this->origin,
                    'destination' => $this->destination,
                    'type' => $this->type,
                ]),
            );
        }
    }

    public function divisions()
    {
        $this->divisions = Division::query()->when($this->pa_country, fn(Builder $q) => $q->where('country_id', $this->pa_country))->get();
    }
    public function districts()
    {
        $this->districts = District::query()->when($this->pa_division, fn(Builder $q) => $q->where('division_id', $this->pa_division))->get();
    }

    public function updated($property)
    {
        if (in_array($property, $this->fileFields())) {
            $this->storeUploadedFile($property);
        }

        if ($property == 'pa_country') {
            $this->divisions();
        }
        if ($property == 'pa_division') {
            $this->districts();
        }
    }

    protected function storeUploadedFile(string $field): void
    {
        $file = $this->{$field} ?? null;

        // Only proceed if it's a real uploaded file (not a string from edit state)
        if ($file instanceof UploadedFile) {
            $path = $this->storeEvisaDocuments($file, 'public', 'e-visa-bookings');
            $this->uploadedFiles[$field] = $path;
        }
    }

    protected function fileFields()
    {
        return ['med_appointment_doc', 'med_previous_doc', 'busi_invitation', 'busi_company_doc', 'busi_other_doc', 'fam_invitation', 'fam_rel_with_invitor', 'fam_invitor_passport', 'fam_invitor_other_doc', 'uni_admission_letter', 'uni_immi_approval', 'uni_other_doc', 'work_permit', 'work_recruiter', 'work_other_doc', 'passport_doc', 'bank_state_doc', 'bank_solvency_doc', 'busi_trade_license', 'busi_office_pad', 'busi_visiting_card', 'sholder_noc', 'sholder_visiting_card', 'sholder_pay_slip', 'stu_id_card', 'stu_last_certificate', 'govt_prof_doc', 'retired_doc'];
    }

    public function proceedToEvisaBooking()
    {
        // Validate required fields manually
        if (!$this->selected_fee) {
            $this->error('Please select a visa fee before proceeding.');
            return;
        }

        // Prepare booking data for session storage
        $bookingData = [
            'total_travellers' => $this->total_travellers,
            'selected_fee' => $this->fee_per_person,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'nationality' => $this->nationality,
            'birth_country' => $this->birth_country,
            'nid' => $this->nid,
            'email' => $this->email,
            'phone' => $this->phone,
            'passport_number' => $this->passport_number,
            'passport_issue_date' => $this->passport_issue_date,
            'passport_exp_date' => $this->passport_exp_date,
            'pa_house_no' => $this->pa_house_no,
            'pa_address' => $this->pa_address,
            'pa_zip_code' => $this->pa_zip_code,
            'pa_country' => $this->pa_country,
            'pa_division' => $this->pa_division,
            'pa_district' => $this->pa_district,
            'des_address' => $this->des_address,
            'des_phone' => $this->des_phone,
            'des_post_code' => $this->des_post_code,
            'arr_date' => $this->arr_date,
            'entry_port' => $this->entry_port,
            'purpose' => $this->purpose,
        ];

        // Store data in session
        Session::put('evisa_booking_data', $bookingData);
        Session::put('evisa_booking_files', $this->uploadedFiles);

        if (Auth::check()) {
            return redirect()->route('frontend.visa.booking', [
                'slug' => $this->visa->slug,
                'origin' => $this->visa->origin_country,
                'destination' => $this->visa->destination_country,
                'type' => $this->type,
            ]);
        } else {
            // User is not authenticated, dispatch event to show login modal
            $this->dispatch(
                'showLoginModal',
                route('frontend.visa.booking', [
                    'slug' => $this->visa->slug,
                    'origin' => $this->visa->origin_country,
                    'destination' => $this->visa->destination_country,
                    'type' => $this->type,
                ]),
            );
        }
    }
}; ?>

<div class="bg-gray-100">
    <livewire:login-modal-component />
    <section
        class="relative items-center bg-[url('https://massagetourtravels.com/assets/images/bg/home-bg.png')] bg-no-repeat bg-cover bg-center py-20 z-10">
        <div class="absolute inset-0 bg-slate-900/40"></div>
        <div class="relative py-12 md:py-20">
            <div class="max-w-6xl mx-auto">
                <livewire:home-search-component />
            </div>
            <!--end container-->
        </div>
    </section>

    <div class="p-6">
        <div x-data="{ tab: 'processing' }" class="bg-white p-6 rounded-lg shadow-md">
            <!-- Tabs -->
            <div class="flex border-b pb-3 justify-evenly">
                <button @click="tab = 'basic'" class="px-4 py-2 border rounded-lg"
                    :class="tab == 'basic' ? 'bg-green-500 font-semibold text-white' : 'bg-white'">Basic
                    Information</button>
                <button @click="tab = 'documents'" class="px-4 py-2 border rounded-lg"
                    :class="tab == 'documents' ? 'bg-green-500 font-semibold text-white' : 'bg-white'">Documents
                    Checklist</button>
                <button @click="tab = 'processing'" class="px-4 py-2 border rounded-lg"
                    :class="tab == 'processing' ? 'bg-green-500 font-semibold text-white' : 'bg-white'">Processing Time
                    &
                    Fees</button>
                <button @click="tab = 'downloads'" class="px-4 py-2 border rounded-lg"
                    :class="tab == 'downloads' ? 'bg-green-500 font-semibold text-white' : 'bg-white'">Forms
                    Downloads</button>
                <button @click="tab = 'consultant'" class="px-4 py-2 border rounded-lg"
                    :class="tab == 'consultant' ? 'bg-green-500 font-semibold text-white' : 'bg-white'">Consultant
                    Info</button>
                <button @click="tab = 'faq'" class="px-4 py-2 border rounded-lg"
                    :class="tab == 'faq' ? 'bg-green-500 font-semibold text-white' : 'bg-white'">FAQ</button>
            </div>

            <!-- Tab Content -->
            <div class="mt-4">
                <!-- Processing Time & Fees -->
                <div x-show="tab == 'processing'">
                    <div class="bg-gray-200 p-4 rounded-lg">
                        <table class="w-full bg-white shadow-md rounded-lg">
                            <thead class="bg-gray-300">
                                <tr>
                                    <th class="px-4 py-2">Select</th>
                                    <th class="px-4 py-2">Visa Fee Type</th>
                                    <th class="px-4 py-2">Processing Time & Fees</th>
                                    <th class="px-4 py-2">Fees (BDT)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($visa->visaFees as $fee)
                                    <tr class="border-b">
                                        <td class="px-4 py-2 text-center">
                                            <input type="radio" name="visa_fee" wire:model.live="selected_fee"
                                                class="custom-radio-dot" value="{{ $fee->id }}">
                                        </td>
                                        <td class="px-4 py-2 text-center">{{ $fee->fee_type }}</td>
                                        <td class="px-4 py-2 text-center">{{ $visa->processing_time }} Days</td>
                                        <td class="px-4 py-2 text-center">BDT {{ number_format($fee->fee, 2) }} </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($visa->type !== \App\Enum\VisaType::Evisa)
                        <!-- Booking Section -->
                        <div class="mt-6 bg-white p-4">
                            <div class="flex space-x-10 bg-white">
                                <div class="text-md">
                                    <p>For Home Delivery Service please Click Book Now</p>
                                    <p class="text-md">(Note: Number of Person and Terms and Condition)</p>
                                </div>

                                <div class="text-md text-gray-700 w-1/3">
                                    <p class="font-semibold">Visa Fee + Service Fee</p>
                                    <p>BDT {{ number_format($fee_per_person, 2) }} Per Person</p>
                                    <p>Convenient Fee BDT {{ number_format($convenient_fee, 2) }} Per Visit</p>
                                    <p class="font-semibold">Total: BDT {{ number_format($total_fee, 2) }}</p>
                                </div>

                                <div>
                                    <label class="text-gray-700 text-sm mb-1">Total Traveller</label>
                                    <input type="number" wire:model.live="total_travellers"
                                        class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-green-400">
                                </div>

                                <div>
                                    <label class="text-gray-700 text-sm mb-1">Documents Collection Date <span
                                            class="font-semibold text-red-500">*</span></label>
                                    <input type="date" wire:model="document_collection_date"
                                        class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-green-400"
                                        required>
                                </div>
                            </div>
                            <div class="text-center">
                                <a wire:click="proceedToBooking"
                                    class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 w-36 rounded text-sm text-center hover:cursor-pointer">
                                    Book Now
                                </a>
                            </div>
                        </div>
                    @else
                        <x-form wire:submit="proceedToEvisaBooking">
                            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-6">
                                <!-- Left Section -->
                                <div class="xl:col-span-2">
                                    <div class="h-full flex flex-col justify-evenly">
                                        <x-devider title="Personal Information" />
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4" x-cloak>
                                            <x-input label="First Name" wire:model="first_name"
                                                placeholder="Enter First Name" class="custome-input-field" required />
                                            <x-input label="Last Name" wire:model="last_name"
                                                placeholder="Enter Last Name" class="custome-input-field" required />
                                            <x-input type="email" label="E-mail Address" wire:model="email"
                                                placeholder="Enter E-mail Address" class="custome-input-field"
                                                required />
                                            <x-input label="Mobile Phone" wire:model="phone"
                                                placeholder="Enter Phone Number" class="custome-input-field" required />
                                            <x-datetime label="Date of Birth" wire:model="dob" class="custom-date-field"
                                                required />
                                            <x-choices label="Gender" wire:model="gender" placeholder="Select Gender"
                                                :options="$genders" single class="custom-select-field" required />
                                            <x-input label="Nationality" wire:model="nationality"
                                                placeholder="Enter Nationality" class="custome-input-field" required />
                                            <x-choices label="Country of Birth" wire:model="birth_country"
                                                placeholder="Select Country" single class="custom-select-field"
                                                :options="$countries" required />
                                            <x-input label="NID" wire:model="nid" placeholder="Enter NID Number"
                                                class="custome-input-field" required />
                                        </div>

                                        <x-devider title="Passport Information" />
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <x-input label="Passport Number" wire:model="passport_number"
                                                placeholder="Enter Passport Number" class="custome-input-field"
                                                required />
                                            <x-datetime label="Issue Date" wire:model="passport_issue_date"
                                                class="custom-date-field" required />
                                            <x-datetime label="Expiry Date" wire:model="passport_exp_date"
                                                class="custom-date-field" required />
                                        </div>

                                        <x-devider title="Present Information" />
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4" x-cloak>
                                            <x-input label="House/Street No" wire:model="pa_house_no"
                                                placeholder="Enter House/Street No" class="custome-input-field" />
                                            <x-input label="Village/Town/City/Address" wire:model="pa_address"
                                                placeholder="Enter Village/Town/City/Address"
                                                class="custome-input-field" />
                                            <x-choices label="Country" wire:model.live="pa_country"
                                                placeholder="Select Country" single class="custom-select-field"
                                                :options="$countries" required />
                                            <x-choices label="Division/State" wire:model.live="pa_division"
                                                placeholder="Select Division/State" single class="custom-select-field"
                                                :options="$divisions" required />
                                            <x-choices label="District/State" wire:model="pa_district"
                                                placeholder="Select District/State" single class="custom-select-field"
                                                :options="$districts" required />
                                            <x-input label="Zip Code" wire:model="pa_zip_code"
                                                placeholder="Enter Zip Code" class="custome-input-field" />
                                        </div>

                                        <x-devider title="Travel Information" />
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4" x-cloak>
                                            <x-input label="Address in Destination" wire:model="des_address"
                                                placeholder="Enter Address in Destination"
                                                class="custome-input-field" />
                                            <x-input label="Destination Contact Number" wire:model="des_phone"
                                                placeholder="Destination Contact Number"
                                                class="custome-input-field" />
                                            <x-input label="Post Code" wire:model="des_post_code"
                                                placeholder="Enter Post Code" class="custome-input-field" />
                                            <x-input label="Entry Port" wire:model="entry_port"
                                                placeholder="Enter Entry Port" class="custome-input-field" />
                                            <x-input label="Travel Document" value="International" readonly
                                                class="custome-input-field" />
                                            <x-datetime label="Arrival Date" wire:model="arr_date"
                                                class="custom-date-field" required />
                                            <x-choices label="Purpose" wire:model.live="purpose" :options="$visaPurposes"
                                                required placeholder="Select Purpose" single
                                                class="custom-select-field" />
                                        </div>

                                        @if ($purpose == 1)
                                            <x-devider title="File Upload For Business Purpose" class="mt-6" />
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <x-file label="Company Invitation" wire:model="busi_invitation" />
                                                <x-file label="Company Documents" wire:model="busi_company_doc" />
                                                <x-file label="Other Documents" wire:model="busi_other_doc" />
                                            </div>
                                        @elseif ($purpose == 2)
                                            <x-devider title="File Upload For Student Purpose" class="mt-6" />
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <x-file label="University Admissions Letter"
                                                    wire:model="uni_admission_letter" />
                                                <x-file label="Immigration Approval Copy"
                                                    wire:model="uni_immi_approval" />
                                                <x-file label="Other Documents" wire:model="uni_other_doc" />
                                            </div>
                                        @elseif ($purpose == 3)
                                            <x-devider title="File Upload For Family Visit" class="mt-6" />
                                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                                <x-file label="Invitation Letter / Other Required Copy"
                                                    wire:model="fam_invitation" />
                                                <x-file label="Relationship Documents with Inviter"
                                                    wire:model="fam_rel_with_invitor" />
                                                <x-file label="Invitor PASSPORT/ ID and Visa Copy"
                                                    wire:model="fam_invitor_passport" />
                                                <x-file label="Invitor Other Documents"
                                                    wire:model="fam_invitor_other_doc" />
                                            </div>
                                        @elseif ($purpose == 4)
                                            <x-devider title="File Upload For Medical Patient" class="mt-6" />
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <x-file label="Doctor Appointment and Invitations"
                                                    wire:model="med_appointment_doc" />
                                                <x-file label="Previous Medical Documents"
                                                    wire:model="med_previous_doc" />
                                            </div>
                                        @elseif ($purpose == 6)
                                            <x-devider title="File Upload For Worker Visa" class="mt-6" />
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <x-file label="Work Permit" wire:model="work_permit" />
                                                <x-file label="Recruiter Company Documents"
                                                    wire:model="work_recruiter" />
                                                <x-file label="Other Documents" wire:model="work_other_doc" />
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Right Section -->
                                <div class="xl:col-span-1">
                                    <x-card class="h-full flex flex-col">
                                        <x-devider title="Document Upload" />
                                        <div
                                            class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 text-sm rounded-md">
                                            <p class="font-bold mb-1">Please read before proceeding:</p>
                                            <ul class="list-disc pl-5 space-y-1">
                                                <li>Please check documents checklist and upload according, less docs may
                                                    not
                                                    entertained.</li>
                                                <li>Select number of Passenger and pay accordingly.</li>
                                                <li>Orginal Copy with duly Signed.</li>
                                                <li>Only in English and Notary (PDF/JPG).</li>
                                                <li>Please Compressed to reduced Size.</li>
                                                <li><span class="text-red-600 font-semibold">If any Documents are not
                                                        clear
                                                        you may ask for further.</span></li>
                                            </ul>
                                        </div>

                                        <div class="space-y-1.5 grid grid-cols-1 md:grid-cols-2 md:gap-5">
                                            <x-file label="Passport (Minimum Validity 06 Months)"
                                                wire:model="passport_doc" />
                                            <x-file label="Bank Statement (Last 06 Months)"
                                                wire:model="bank_state_doc" />
                                            <x-file label="Bank Solvency (For Parents or LC Balance)"
                                                wire:model="bank_solvency_doc" />

                                            <x-file label="Updated Trade License" wire:model="busi_trade_license" />
                                            <x-file label="Official Pad duly signed" wire:model="busi_office_pad" />
                                            <x-file label="Visiting Card" wire:model="busi_visiting_card" />

                                            <x-file label="NOC" wire:model="sholder_noc" />
                                            <x-file label="Visiting Card" wire:model="sholder_visiting_card" />
                                            <x-file label="Pay Slip/Salary Certificate"
                                                wire:model="sholder_pay_slip" />

                                            <x-file label="Student Card" wire:model="stu_id_card" />
                                            <x-file
                                                label="Last Certificate and Parent's Professional Docs and Permission Letter"
                                                wire:model="stu_last_certificate" />

                                            <x-file label="GO/Memo Must with other professional Documents"
                                                wire:model="govt_prof_doc" />

                                            <x-file label="For Retired Person: Related Documents"
                                                wire:model="retired_doc" />
                                        </div>
                                    </x-card>
                                </div>

                            </div>
                            <div class="text-center">
                                <a wire:click="proceedToEvisaBooking"
                                    class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-6 w-40 rounded text-sm md:text-base text-center hover:cursor-pointer">
                                    Submit
                                </a>
                            </div>
                        </x-form>
                    @endif

                </div>

                <!-- Other Tabs Content -->
                <div x-show="tab == 'basic'" class="p-4 bg-gray-100 rounded-lg" x-cloak>
                    <p class="text-gray-700">{!! $visa->basic_info !!}</p>
                </div>
                <div x-show="tab == 'documents'" class="p-4 bg-gray-100 rounded-lg" x-cloak>
                    <p class="text-gray-700">{!! $visa->checklists !!}</p>
                </div>
                <div x-show="tab == 'downloads'" class="p-4 bg-gray-100 rounded-lg" x-cloak>
                    <p class="text-gray-700">Forms Downloads content goes here.</p>
                </div>
                <div x-show="tab == 'consultant'" class="p-4 bg-gray-100 rounded-lg" x-cloak>
                    <p class="text-gray-700">Consultant Information content goes here.</p>
                </div>
                <div x-show="tab == 'faq'" class="p-4 bg-gray-100 rounded-lg" x-cloak>
                    <p class="text-gray-700">{!! $visa->faq !!}</p>
                </div>
            </div>
        </div>
    </div>
</div>
