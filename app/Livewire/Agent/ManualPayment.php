<?php

namespace App\Livewire\Agent;

use App\Models\ChartOfAccount;
use App\Models\Income;
use App\Enum\TransactionStatus;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Mary\Traits\Toast;

#[Layout('components.layouts.partner')]
#[Title('Manual Payment')]
class ManualPayment extends Component
{
    use Toast, WithFileUploads;

    public $account_id = '';
    public $amount = '';
    public $reference = '';
    public $remarks = '';
    public $payment_slip;
    public $bank_accounts = [];

    protected $rules = [
        'account_id' => 'required|exists:chart_of_accounts,id',
        'amount' => 'required|numeric|min:1',
        'reference' => 'required|string|max:255',
        'remarks' => 'nullable|string|max:500',
        'payment_slip' => 'required|image|max:2048',
    ];

    protected $messages = [
        'account_id.required' => 'Please select a bank account.',
        'account_id.exists' => 'Selected bank account is invalid.',
        'amount.required' => 'Amount is required.',
        'amount.numeric' => 'Amount must be a number.',
        'amount.min' => 'Amount must be at least 1.',
        'reference.required' => 'Reference is required.',
        'reference.max' => 'Reference must not exceed 255 characters.',
        'remarks.max' => 'Remarks must not exceed 500 characters.',
        'payment_slip.required' => 'Payment slip is required.',
        'payment_slip.image' => 'Payment slip must be an image.',
        'payment_slip.max' => 'Payment slip must not exceed 2MB.',
    ];

    public function mount()
    {
        $this->loadBankAccounts();
    }

    public function loadBankAccounts()
    {
        // First find the Bank category
        $bankCategory = ChartOfAccount::where('name', 'Bank')
            ->where('type', 'asset')
            ->first();

        if ($bankCategory) {
            // Get all accounts under Bank category
            $this->bank_accounts = ChartOfAccount::where('parent_id', $bankCategory->id)
                ->where('type', 'asset')
                ->orderBy('name')
                ->get();
        } else {
            $this->bank_accounts = collect();
        }
    }

    public function submitPayment()
    {
        $this->validate();

        try {
            // Upload payment slip
            $paymentSlipPath = $this->payment_slip->store('payment-slips', 'public');

            // Create income record with pending status
            Income::create([
                'agent_id' => Auth::user()->agent->id,
                'account_id' => $this->account_id,
                'amount' => $this->amount,
                'reference' => $this->reference,
                'remarks' => $this->remarks,
                'payment_slip' => $paymentSlipPath,
                'status' => TransactionStatus::PENDING,
                'created_by' => Auth::id(),
            ]);

            // Reset form
            $this->reset(['account_id', 'amount', 'reference', 'remarks', 'payment_slip']);

            $this->success('Payment request submitted successfully! It will be reviewed by admin.');
        } catch (\Exception $e) {
            $this->error('Failed to submit payment request. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.agent.manual-payment');
    }
}
