<?php

use App\Models\Order;
use App\Models\EvisaBookingDetails;
use App\Models\VisaBooking;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] #[Title('E-visa Documents')] class extends Component {
    public Order $order;
    public ?VisaBooking $booking = null;
    public ?EvisaBookingDetails $detail = null;

    /** Document groups and labels */
    protected array $groups = [
        'General' => [
            'passport_doc' => 'Passport',
        ],
        'Medical' => [
            'med_appointment_doc' => 'Medical Appointment',
            'med_previous_doc' => 'Previous Medical',
        ],
        'Business' => [
            'busi_invitation' => 'Business Invitation',
            'busi_company_doc' => 'Company Document',
            'busi_other_doc' => 'Other Business Doc',
            'busi_trade_license' => 'Trade License',
            'busi_office_pad' => 'Office Pad',
            'busi_visiting_card' => 'Visiting Card',
        ],
        'Family' => [
            'fam_invitation' => 'Family Invitation',
            'fam_rel_with_invitor' => 'Relation with Invitor',
            'fam_invitor_passport' => 'Invitor Passport',
            'fam_invitor_other_doc' => 'Invitor Other Doc',
        ],
        'University' => [
            'uni_admission_letter' => 'Admission Letter',
            'uni_immi_approval' => 'Immigration Approval',
            'uni_other_doc' => 'Other University Doc',
        ],
        'Work' => [
            'work_permit' => 'Work Permit',
            'work_recruiter' => 'Recruiter Doc',
            'work_other_doc' => 'Other Work Doc',
        ],
        'Bank' => [
            'bank_state_doc' => 'Bank Statement',
            'bank_solvency_doc' => 'Bank Solvency',
        ],
        'Employee' => [
            'sholder_noc' => 'NOC',
            'sholder_visiting_card' => 'Visiting Card',
            'sholder_pay_slip' => 'Pay Slip',
        ],
        'Student' => [
            'stu_id_card' => 'Student ID',
            'stu_last_certificate' => 'Last Certificate',
        ],
        'Other' => [
            'govt_prof_doc' => 'Govt Profession Doc',
            'retired_doc' => 'Retired Doc',
        ],
    ];

    /** Mount: load order + relations */
    public function mount(Order $order): void
    {
        $this->order = $order->load(['user', 'sourceable']);

        if ($this->order->sourceable_type == VisaBooking::class && $this->order->sourceable) {
            $this->order->sourceable->loadMissing(['visa', 'evisa_booking_detail']);
            $this->booking = $this->order->sourceable;
            $this->detail = $this->booking->evisa_booking_detail;
        }
    }

    public function download(string $column)
    {
        // Must have detail loaded
        if (!$this->detail) {
            abort(404, 'No detail row found.');
        }

        // Only allow known file fields (reuse your list if you keep one on the component)
        $allowed = ['med_appointment_doc', 'med_previous_doc', 'busi_invitation', 'busi_company_doc', 'busi_other_doc', 'fam_invitation', 'fam_rel_with_invitor', 'fam_invitor_passport', 'fam_invitor_other_doc', 'uni_admission_letter', 'uni_immi_approval', 'uni_other_doc', 'work_permit', 'work_recruiter', 'work_other_doc', 'passport_doc', 'bank_state_doc', 'bank_solvency_doc', 'busi_trade_license', 'busi_office_pad', 'busi_visiting_card', 'sholder_noc', 'sholder_visiting_card', 'sholder_pay_slip', 'stu_id_card', 'stu_last_certificate', 'govt_prof_doc', 'retired_doc'];

        if (!in_array($column, $allowed, true)) {
            abort(403, 'Not allowed.');
        }

        $value = $this->detail->{$column} ?? null;

        if (!is_string($value) || $value === '') {
            abort(404, 'File not found.');
        }

        // If stored on disk (recommended)
        if (!Str::startsWith($value, ['http://', 'https://'])) {
            $disk = 'public';

            if (!Storage::disk($disk)->exists($value)) {
                abort(404, 'File not found on disk.');
            }

            // Let Laravel stream the file with proper headers
            $filename = basename($value);
            return Storage::disk($disk)->download($value, $filename);
        }

        // If it's an external absolute URL:
        // Try to stream the remote bytes through this response so the browser downloads it.
        // (Note: Some hosts may block this; in that case, consider storing files on your own disk.)
        $urlPath = parse_url($value, PHP_URL_PATH) ?? '';
        $filename = basename($urlPath) ?: 'document';

        return response()->streamDownload(function () use ($value) {
            // Simple passthrough; you can swap to Guzzle/Http::get if you prefer
            $ctx = stream_context_create(['http' => ['timeout' => 15]]);
            $stream = @fopen($value, 'rb', false, $ctx);
            if ($stream) {
                while (!feof($stream)) {
                    echo fread($stream, 8192);
                    @ob_flush();
                    flush();
                }
                fclose($stream);
            }
        }, $filename);
    }

    /** Pass data to Blade via with() */
    public function with(): array
    {
        return [
            'order' => $this->order,
            'booking' => $this->booking,
            'detail' => $this->detail,
            'groups' => $this->groups,
        ];
    }
}; ?>

<div>
    <x-header title="E-visa Documents" size="text-xl" separator class="bg-white px-2 pt-2">

        <x-slot:actions>
            <x-button label="Back" link="/admin/e-visa/booking/list" class="btn-sm btn-primary" icon="fas.arrow-left" />
        </x-slot:actions>
    </x-header>
    <div class="space-y-4">

        {{-- No details case --}}
        @if (!$booking || !$detail)
            <x-alert title="No Documents Found" icon="o-information-circle" class="bg-yellow-50 text-yellow-800">
                This order is not linked to a Visa Booking with E-Visa details yet.
            </x-alert>
        @else
            {{-- Purpose (text) quick view --}}
            @if ($detail && $detail->purpose)
                <div class="bg-white shadow p-4 md:p-6">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0">
                            <x-icon name="o-chat-bubble-left-right" class="w-6 h-6 text-emerald-600" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-zinc-900">Purpose</h3>
                            <p class="text-zinc-700">
                                @php
                                    // If you cast purpose to VisaPurpose, it will be an enum instance
                                    $purpose = $detail->purpose;
                                    if ($purpose instanceof \App\Enum\VisaPurpose) {
                                        echo e($purpose->label());
                                    } else {
                                        echo e((string) $purpose);
                                    }
                                @endphp
                            </p>
                        </div>
                    </div>
                </div>
            @endif


            {{-- Document Groups --}}
            @foreach ($groups as $groupTitle => $fields)
                @php
                    // Only keep fields that have a non-empty value
                    $nonEmpty = collect($fields)->filter(function ($label, $column) use ($detail) {
                        return filled($detail->{$column});
                    });
                @endphp

                @if ($nonEmpty->isNotEmpty())
                    <div class="bg-white shadow">
                        <div class="px-4 md:px-6 py-2">
                            <x-devider title="{{ $groupTitle }}" />
                        </div>

                        <div class="p-4 md:px-6 md:py-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($nonEmpty as $column => $label)
                                    @php
                                        $value = $detail->{$column};
                                    @endphp

                                    @if (is_string($value) && $value !== '')
                                        @php
                                            // Build URL from storage path OR accept absolute URL
                                            $url = \Illuminate\Support\Str::startsWith($value, ['http://', 'https://'])
                                                ? $value
                                                : \Illuminate\Support\Facades\Storage::disk('public')->url($value);

                                            $path = parse_url($url, PHP_URL_PATH) ?? '';
                                            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                            $isPdf = $ext === 'pdf';
                                        @endphp

                                        <div class="border rounded-xl p-4 hover:shadow transition bg-zinc-50">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex items-center gap-2">
                                                    <x-icon name="o-paper-clip" class="w-5 h-5 text-zinc-500" />
                                                    <span class="font-medium text-zinc-800">{{ $label }}</span>
                                                </div>
                                            </div>

                                            @if ($isImage)
                                                <a href="{{ $url }}" target="_blank" rel="noopener"
                                                    class="block">
                                                    <img src="{{ $url }}" alt="{{ $label }}"
                                                        class="w-full h-40 object-cover rounded-lg border" />
                                                </a>
                                            @elseif ($isPdf)
                                                <div
                                                    class="h-40 bg-white border rounded-lg flex items-center justify-center">
                                                    <x-icon name="o-document" class="w-10 h-10 text-red-600" />
                                                </div>
                                            @else
                                                <div
                                                    class="h-40 bg-white border rounded-lg flex items-center justify-center">
                                                    <x-icon name="o-document" class="w-10 h-10 text-zinc-500" />
                                                </div>
                                            @endif

                                            <div class="mt-3 flex items-center gap-2">
                                                <x-button icon="o-eye" class="btn-sm bg-primary text-white" external
                                                    link="{{ $url }}">
                                                    View
                                                </x-button>
                                                <x-button icon="o-arrow-down-tray" class="btn-sm bg-zinc-800 text-white"
                                                    wire:click.prevent="download('{{ $column }}')">
                                                    Download
                                                </x-button>
                                            </div>
                                        </div>
                                    @else
                                        {{-- Value is not a string path (could be enum/array/null) --}}
                                        <div class="border rounded-xl p-4 bg-zinc-50">
                                            <div class="flex items-center gap-2">
                                                <x-icon name="o-paper-clip" class="w-5 h-5 text-zinc-500" />
                                                <span class="font-medium text-zinc-800">{{ $label }}</span>
                                            </div>
                                            <p class="mt-2 text-sm text-zinc-500">Not provided</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

        @endif
    </div>
</div>
