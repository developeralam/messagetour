<?php

use App\Models\User;
use App\Enum\UserType;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Enum\UserStatus;
use App\Models\Customer;
use App\Models\District;
use App\Models\Division;
use App\Enum\CountryStatus;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Jobs\SendCustomerMailJob;
use App\Services\TransactionService;
use App\Models\ChartOfAccount;
use App\Models\Transactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Traits\InteractsWithImageUploads;

new #[Layout('components.layouts.admin')] #[Title('Customer List')] class extends Component {
    use WithPagination, Toast, WithFileUploads, InteractsWithImageUploads;
    public array $headers;
    public string $search = '';
    public $image_link;
    public $statuses = [];
    public $countries = [];
    public bool $createModal = false;
    public bool $editModal = false;
    public Customer $customer;
    public bool $smsModal = false;
    public bool $emailModal = false;
    public array $selectedCustomers;
    public $body;
    public $subject;
    public $email_body;

    #[Rule('required')]
    public $name;

    #[Rule('required')]
    public $email;

    #[Rule('nullable')]
    public $address;

    #[Rule('nullable')]
    public $secondary_address;

    #[Rule('nullable')]
    public $opening_balance;

    #[Rule('nullable')]
    public $country_id;

    #[Rule('nullable')]
    public $division_id;

    #[Rule('nullable')]
    public $district_id;

    #[Rule('nullable')]
    public $image;

    #[Rule('required')]
    public $password;

    #[Rule('required|same:password')]
    public $confirmation_password;

    #[Rule('required')]
    public $status;

    /**
     * Called when the component is initialized.
     * Loads default data such as table headers, statuses, and countries.
     * @return void
     */
    public function mount(): void
    {
        $this->headers = $this->headers();
        $this->statuses = UserStatus::getStatuses();
        $this->countries = Country::where('status', CountryStatus::Active)->orderBy('name', 'asc')->get();
        $this->status = UserStatus::Active;
    }

    /**
     * Store a new customer record along with its associated User.
     *
     * @return void
     */
    public function storeCustomer(): void
    {
        $this->validate();

        try {
            DB::transaction(function () {
                // Store customer image if uploaded
                $storedImagePath = $this->image ? $this->optimizeAndStoreImage($this->image, 'public', 'customer', null, null, 75) : '';

                // Create a new User record
                $user = User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => Hash::make($this->password),
                    'type' => UserType::Customer,
                    'status' => $this->status,
                ]);

                // Create the associated Customer record
                $customer = Customer::create([
                    'user_id' => $user->id,
                    'address' => $this->address,
                    'country_id' => $this->country_id ?: null,
                    'division_id' => $this->division_id ?: null,
                    'district_id' => $this->district_id ?: null,
                    'secondary_address' => $this->secondary_address,
                    'opening_balance' => $this->opening_balance,
                    'image' => $storedImagePath ?? '',
                ]);

                // Handle opening balance transaction if amount is provided
                if ($this->opening_balance && $this->opening_balance != 0) {
                    $this->recordCustomerOpeningBalanceTransaction($customer, $this->opening_balance);
                }
            });

            $this->success('Customer Added Successfully');

            // Reset form fields
            $this->reset(['address', 'secondary_address', 'country_id', 'division_id', 'district_id', 'name', 'email', 'password', 'confirmation_password', 'status', 'image']);
            $this->createModal = false;
        } catch (\Throwable $th) {
            $this->resetForm();
            $this->createModal = false;
            $this->error(env('APP_DEBUG') ? $th->getMessage() : 'Something went wrong.');
        }
    }

    /**
     * Populate form fields for editing an existing customer.
     *
     * @param Customer $customer
     * @return void
     */
    public function edit(Customer $customer): void
    {
        $this->customer = $customer;
        $this->name = $customer->user->name ?? '';
        $this->email = $customer->user->email ?? '';
        $this->status = $customer->user->status ?? '';
        $this->address = $customer->address ?? '';
        $this->country_id = $customer->country_id ?? '';
        $this->division_id = $customer->division_id ?? '';
        $this->district_id = $customer->district_id ?? '';
        $this->secondary_address = $customer->secondary_address ?? '';
        $this->opening_balance = $customer->opening_balance ?? 0;
        $this->image_link = $customer->image_link;
        $this->editModal = true;
    }

    /**
     * Update an existing customer record.
     *
     * @return void
     */
    public function updateCustomer(): void
    {
        $this->validate([
            'password' => 'nullable',
            'confirmation_password' => 'nullable|same:password',
        ]);

        try {
            DB::transaction(function () {
                // Store the old opening balance for comparison
                $oldOpeningBalance = $this->customer->opening_balance ?? 0;
                $newOpeningBalance = $this->opening_balance ?? 0;

                // Update customer image if a new one is uploaded
                $storedImagePath = $this->image ? $this->optimizeAndUpdateImage($this->image, $this->customer->image, 'public', 'customer', null, null, 75) : $this->customer->image;

                // Update associated User record
                $this->customer->user->update([
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => $this->password != null ? Hash::make($this->password) : $this->customer->user->password,
                    'status' => $this->status,
                ]);

                // Update Customer record
                $this->customer->update([
                    'address' => $this->address,
                    'country_id' => $this->country_id ?: null,
                    'division_id' => $this->division_id ?: null,
                    'district_id' => $this->district_id ?: null,
                    'secondary_address' => $this->secondary_address,
                    'opening_balance' => $newOpeningBalance,
                    'image' => $storedImagePath ?? '',
                ]);

                // Handle opening balance changes
                if ($oldOpeningBalance != $newOpeningBalance) {
                    $this->handleCustomerOpeningBalanceUpdate($this->customer, $oldOpeningBalance, $newOpeningBalance);
                }
            });

            $this->success('Customer Updated Successfully');
            $this->resetForm();
            $this->editModal = false;
        } catch (\Throwable $th) {
            $this->resetForm();
            $this->editModal = false;
            $this->error(env('APP_DEBUG') ? $th->getMessage() : 'Something went wrong.');
        }
    }

    /**
     * Reset pagination when a property changes.
     *
     * @param string $property
     * @return void
     */
    public function updated($property): void
    {
        if (!is_array($property) && $property != '') {
            $this->resetPage();
        }
    }

    public function countrySearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';

        $countries = Country::where('status', CountryStatus::Active)->where('name', 'like', $searchTerm)->limit(5)->get();

        $this->countries = $countries;
    }

    public function divisionSearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';
        $divisions = Division::where('country_id', $this->country_id)->where('name', 'like', $searchTerm)->limit(5)->get();

        $this->divisions = $divisions;
    }

    public function districtSearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';
        $districts = District::where('division_id', $this->division_id)->where('name', 'like', $searchTerm)->limit(5)->get();

        $this->districts = $districts;
    }

    /**
     * Delete a customer and their associated user.
     *
     * @param Customer $customer
     * @return void
     */
    public function delete(Customer $customer): void
    {
        try {
            $customer->update([
                'action_by' => auth()->user()->id,
            ]);
            $customer->user()->delete();
            $customer->delete();
            $this->success('Customer Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }

    /**
     * Define the table headers for customers list.
     *
     * @return array
     */
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'image', 'label' => 'Customer'], ['key' => 'user.name', 'label' => 'Customer Name'], ['key' => 'user.email', 'label' => 'Email Address'], ['key' => 'country.name', 'label' => 'Country'], ['key' => 'division.name', 'label' => 'Division'], ['key' => 'district.name', 'label' => 'District'], ['key' => 'address', 'label' => 'Address'], ['key' => 'secondary_address', 'label' => 'Secondary Address'], ['key' => 'status', 'label' => 'Status'], ['key' => 'action_by', 'label' => 'Last Action By']];
    }

    /**
     * Get a paginated list of customers with their related data.
     *
     * @return \Livewire\WithPagination
     */
    public function customers()
    {
        $term = trim((string) $this->search);
        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $term) . '%';

        return Customer::query()
            ->with(['user', 'country', 'division', 'district', 'actionBy'])
            ->when($term !== '', function ($q) use ($like) {
                $q->where(function ($q) use ($like) {
                    $q->whereHas('user', function ($uq) use ($like) {
                        $uq->where('name', 'like', $like)->orWhere('email', 'like', $like);
                    })
                        ->orWhere('address', 'like', $like)
                        ->orWhere('secondary_address', 'like', $like)
                        ->orWhereHas('country', fn($cq) => $cq->where('name', 'like', $like))
                        ->orWhereHas('division', fn($dq) => $dq->where('name', 'like', $like))
                        ->orWhereHas('district', fn($dq) => $dq->where('name', 'like', $like))
                        ->orWhere('opening_balance', 'like', $like);
                });
            })
            ->latest()
            ->paginate(20);
    }

    /**
     * Send SMS to selected customers.
     *
     * @return void
     */
    public function sendSms(): void
    {
        try {
            $subscribers = Customer::whereIn('id', $this->selectedCustomers)->pluck('phone')->toArray();

            $phoneNumbers = implode(',', $subscribers);
            sms_send($phoneNumbers, $this->body);

            $this->body = '';
            $this->success('SMS Sent Successfully');
            $this->smsModal = false;
        } catch (\Throwable $th) {
            $this->smsModal = false;
            $this->error($th->getMessage());
        }
    }

    /**
     * Send Email to selected customers.
     *
     * @return void
     */
    public function sendMail(): void
    {
        try {
            $customer_emails = Customer::whereIn('id', $this->selectedCustomers)->with('user')->get()->pluck('user.email')->toArray();

            if (!empty($customer_emails)) {
                $to_email = array_shift($customer_emails);
                $bcc_emails = $customer_emails;
                SendCustomerMailJob::dispatch($to_email, $bcc_emails, $this->subject, $this->email_body);

                $this->subject = '';
                $this->email_body = '';
                $this->success('E-mail Sent Successfully');
                $this->emailModal = false;
            }
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }

    /**
     * Log in as a specific customer.
     *
     * @param int $customerId
     */
    public function login($customerId)
    {
        try {
            $user = User::find($customerId);
            Auth::login($user);
            request()->session()->regenerate();

            return $this->success('Login Successful', redirectTo: '/dashboard');
        } catch (\Throwable $th) {
            return $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    /**
     * Share common data with the view.
     *
     * @return array
     */
    public function with(): array
    {
        return [
            'customers' => $this->customers(),
            'divisions' => Division::when($this->country_id, function ($q) {
                $q->where('country_id', intval($this->country_id));
            })->get(),
            'districts' => District::when($this->division_id, function ($q) {
                $q->where('division_id', intval($this->division_id));
            })->get(),
        ];
    }

    /**
     * Record opening balance transaction for a new customer.
     *
     * @param Customer $customer
     * @param float $amount
     * @return void
     */
    private function recordCustomerOpeningBalanceTransaction(Customer $customer, $amount): void
    {
        // Get or create customer account
        $customerAccount = $this->getOrCreateCustomerAccount($customer);

        // Get or create the equity account for opening balance contra-entry
        $equityAccount = $this->getOrCreateEquityAccount();

        // Determine debit/credit based on amount
        // Positive amount = Customer owes us (Accounts Receivable - Asset account)
        // Negative amount = We owe customer (Accounts Payable - Liability account)
        if ($amount > 0) {
            // Customer owes us - DEBIT Accounts Receivable, CREDIT Equity
            $debitAccountId = $customerAccount->id;
            $creditAccountId = $equityAccount->id;
        } else {
            // We owe customer - DEBIT Equity, CREDIT Accounts Payable
            $debitAccountId = $equityAccount->id;
            $creditAccountId = $customerAccount->id;
            $amount = abs($amount); // Make amount positive for transaction
        }

        // Record the transaction
        TransactionService::recordTransaction([
            'source_type' => Customer::class,
            'source_id' => $customer->id,
            'date' => now()->toDateString(),
            'amount' => $amount,
            'debit_account_id' => $debitAccountId,
            'credit_account_id' => $creditAccountId,
            'description' => "Opening balance for customer: {$customer->user->name}",
            'approved_at' => now(),
        ]);
    }

    /**
     * Handle opening balance update for existing customer.
     *
     * @param Customer $customer
     * @param float $oldBalance
     * @param float $newBalance
     * @return void
     */
    private function handleCustomerOpeningBalanceUpdate(Customer $customer, $oldBalance, $newBalance): void
    {
        $difference = $newBalance - $oldBalance;

        if ($difference == 0) {
            return; // No change needed
        }

        // Get or create customer account
        $customerAccount = $this->getOrCreateCustomerAccount($customer);

        // Get or create the equity account
        $equityAccount = $this->getOrCreateEquityAccount();

        // Determine debit/credit based on difference
        if ($difference > 0) {
            // Balance increased - Customer owes us more
            $debitAccountId = $customerAccount->id;
            $creditAccountId = $equityAccount->id;
        } else {
            // Balance decreased - Customer owes us less
            $debitAccountId = $equityAccount->id;
            $creditAccountId = $customerAccount->id;
            $difference = abs($difference); // Make amount positive for transaction
        }

        // Record the adjustment transaction
        TransactionService::recordTransaction([
            'source_type' => Customer::class,
            'source_id' => $customer->id,
            'date' => now()->toDateString(),
            'amount' => $difference,
            'debit_account_id' => $debitAccountId,
            'credit_account_id' => $creditAccountId,
            'description' => "Opening balance adjustment for customer: {$customer->user->name} (Old: {$oldBalance}, New: {$newBalance})",
            'approved_at' => now(),
        ]);
    }

    /**
     * Get or create customer account in chart of accounts.
     *
     * @param Customer $customer
     * @return ChartOfAccount
     */
    private function getOrCreateCustomerAccount(Customer $customer): ChartOfAccount
    {
        $accountName = "Accounts Receivable - {$customer->user->name}";

        // Try to find existing account
        $account = ChartOfAccount::where('name', $accountName)->first();

        if (!$account) {
            // Create new customer account
            ChartOfAccount::$skipCodeGeneration = true;
            $account = ChartOfAccount::create([
                'code' => $this->getNextCustomerAccountCode(),
                'name' => $accountName,
                'type' => 'asset', // Accounts Receivable is an asset
                'opening_balance' => 0,
                'current_balance' => 0,
            ]);
            ChartOfAccount::$skipCodeGeneration = false;
        }

        return $account;
    }

    /**
     * Get or create equity account for opening balance contra-entry.
     *
     * @return ChartOfAccount
     */
    private function getOrCreateEquityAccount(): ChartOfAccount
    {
        $equityAccount = ChartOfAccount::where('type', 'equity')->whereNull('parent_id')->first();

        if (!$equityAccount) {
            // Create a default equity account if it doesn't exist
            ChartOfAccount::$skipCodeGeneration = true;
            $equityAccount = ChartOfAccount::create([
                'code' => 300,
                'name' => 'Owner\'s Equity',
                'type' => 'equity',
                'opening_balance' => 0,
                'current_balance' => 0,
            ]);
            ChartOfAccount::$skipCodeGeneration = false;
        }

        return $equityAccount;
    }

    /**
     * Get next available code for customer account.
     *
     * @return int
     */
    private function getNextCustomerAccountCode(): int
    {
        $lastCustomerAccount = ChartOfAccount::where('type', 'asset')->where('name', 'like', 'Accounts Receivable%')->orderByDesc('code')->first();

        return $lastCustomerAccount ? $lastCustomerAccount->code + 1 : 105; // Start from 105 if none exist
    }

    /**
     * Helper method to reset form fields.
     *
     * @return void
     */
    private function resetForm()
    {
        $this->reset(['address', 'secondary_address', 'country_id', 'division_id', 'district_id', 'name', 'email', 'password', 'confirmation_password', 'status', 'image', 'opening_balance']);
    }
}; ?>

<div>
    <x-header title="Customer List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Send Sms" @click="$wire.smsModal = true" class="btn-primary btn-sm" />
            <x-button label="Send Mail" @click="$wire.emailModal = true" class="btn-primary btn-sm" />
            <x-button label="Add Customer" icon="o-plus" @click="$wire.createModal = true" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$customers" with-pagination selectable wire:model.live="selectedCustomers">
            @scope('cell_id', $customer, $customers)
                {{ $loop->iteration + ($customers->currentPage() - 1) * $customers->perPage() }}
            @endscope
            @scope('cell_image', $customer)
                <x-avatar image="{{ $customer->image_link ?? '/empty-user.jpg' }}" class="!w-10" />
            @endscope
            @scope('cell_action_by', $customer)
                {{ $customer->actionBy->name ?? 'N/A' }}
            @endscope
            @scope('cell_status', $customer)
                @if ($customer->user->status == \App\Enum\UserStatus::Active)
                    <x-badge value="{{ $customer->user->status->label() }}" class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($customer->user->status == \App\Enum\UserStatus::Inactive)
                    <x-badge value="{{ $customer->user->status->label() }}" class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('actions', $customer)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $customer['id'] }})" wire:confirm="Are you sure?" class="btn-error btn-action"
                        spinner="delete({{ $customer['id'] }})" />
                    <x-button icon="s-pencil-square" wire:click="edit({{ $customer['id'] }})" spinner="edit({{ $customer['id'] }})"
                        class="btn-neutral btn-action" />
                    <x-button icon="fas.right-to-bracket" wire:click="login({{ $customer['user']['id'] }})" class="btn-primary btn-action text-white"
                        spinner="login({{ $customer['user']['id'] }})" />
                </div>
            @endscope
        </x-table>
    </x-card>
    <x-modal wire:model="createModal" title="Add New Customer" size="text-xl" separator boxClass="max-w-6xl">
        <x-form wire:submit="storeCustomer">
            <div class="grid grid-cols-3 gap-4">
                <x-input label="Customer Name" placeholder="Customer Name" wire:model="name" required />
                <x-input label="Customer Email" placeholder="Customer Email Address" wire:model="email" required />
                <x-choices wire:model.live="country_id" :options="$countries" label="Country" placeholder="Select Country" single
                    search-function="countrySearch" searchable />
                <x-choices wire:model.live="division_id" :options="$divisions" label="Division" placeholder="Select Division" single
                    search-function="divisionSearch" searchable />
                <x-choices wire:model.live="district_id" :options="$districts" label="District" placeholder="Select District" single
                    search-function="districtSearch" searchable />
                <x-input label="Primary Address" placeholder="Primary Address" wire:model="address" />
                <x-input label="Secondary Address" placeholder="Secondary Address" wire:model="secondary_address" />
                <x-input type="number" label="Opening Balance" placeholder="Opening Balance" wire:model="opening_balance" />
                <x-password label="Password" placeholder="Password" wire:model="password" required right />
                <x-password label="Confirm Password" placeholder="Confirm Password" wire:model="confirmation_password" required right />
                <x-radio label="Customer Status" :options="$statuses" wire:model="status" />
                <x-file wire:model="image" label="Customer Image" />
            </div>
            <x-slot:actions>
                <x-button label="Close" @click="$wire.createModal = false" class="btn-sm" />
                <x-button type="submit" label="Add Customer" class="btn-primary btn-sm" spinner="storeCustomer" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    <x-modal wire:model="editModal" title="Update Customer - {{ $customer->user->name ?? '' }}" size="text-xl" separator boxClass="max-w-6xl">
        <x-form wire:submit="updateCustomer">
            <div class="grid grid-cols-3 gap-4">
                <x-input label="Customer Name" placeholder="Customer Name" wire:model="name" required />
                <x-input label="Customer Email" placeholder="Customer Email Address" wire:model="email" required />
                <x-choices wire:model.live="country_id" :options="$countries" label="Country" placeholder="Select Country" single
                    search-function="countrySearch" searchable />
                <x-choices wire:model.live="division_id" :options="$divisions" label="Division" placeholder="Select Division" single
                    search-function="divisionSearch" searchable />
                <x-choices wire:model.live="district_id" :options="$districts" label="District" placeholder="Select District" single
                    search-function="districtSearch" searchable />
                <x-input label="Primary Address" placeholder="Primary Address" wire:model="address" />
                <x-input label="Secondary Address" placeholder="Secondary Address" wire:model="secondary_address" />
                <x-input type="number" label="Opening Balance" placeholder="Opening Balance" wire:model="opening_balance" />
                <x-password label="Password" placeholder="Password" wire:model="password" right />
                <x-password label="Confirm Password" placeholder="Confirm Password" wire:model="confirmation_password" right />
                <x-radio label="Customer Status" :options="$statuses" wire:model="status" />
                <x-file wire:model="image" label="Customer Image" />
            </div>
            <x-slot:actions>
                <x-button label="Close" @click="$wire.editModal = false" class="btn-sm" />
                <x-button type="submit" label="Save" class="btn-primary btn-sm" spinner="updateCustomer" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    <x-modal wire:model="smsModal" title="Send Sms To Customer" size="text-lg" separator>
        <x-form wire:submit="sendSms">
            <x-textarea wire:model="body" placeholder="Body" required />
            <x-slot:actions>
                <x-button class="btn-sm" label="Cancel" @click="$wire.smsModal = false" />
                <x-button type="submit" class="btn-primary btn-sm" label="Send SMS" />
            </x-slot>
        </x-form>
    </x-modal>
    <x-modal wire:model="emailModal" title="Send Email To Customer" separator>
        <x-form wire:submit="sendMail">
            <x-input label="Subject" wire:model="subject" required />
            <x-textarea label="Mail Body" wire:model="email_body" required />
            <x-slot:actions>
                <x-button class="btn-sm" label="Cancel" @click="$wire.emailModal = false" />
                <x-button type="submit" class="btn-primary btn-sm" label="Send Email" spinner="sendMail" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
