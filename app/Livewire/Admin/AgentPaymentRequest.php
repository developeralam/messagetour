<?php

namespace App\Livewire\Admin;

use App\Models\AgentPaymentRequest as AgentPaymentRequestModel;
use App\Models\Agent;
use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class AgentPaymentRequest extends Component
{
    use WithPagination;

    public $search = '';
    public $status_filter = '';
    public $selected_request = null;
    public $approval_notes = '';
    public $show_approval_modal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'status_filter' => ['except' => ''],
    ];

    public function render()
    {
        $requests = AgentPaymentRequestModel::with(['agent.user', 'order', 'approvedBy'])
            ->when($this->search, function ($query) {
                $query->whereHas('agent.user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                })->orWhereHas('order', function ($q) {
                    $q->where('tran_id', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status_filter, function ($query) {
                $query->where('status', $this->status_filter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.agent-payment-request', [
            'requests' => $requests
        ]);
    }

    public function showApprovalModal($requestId)
    {
        $this->selected_request = AgentPaymentRequestModel::with(['agent.user', 'order'])->find($requestId);
        $this->approval_notes = '';
        $this->show_approval_modal = true;
    }

    public function closeApprovalModal()
    {
        $this->show_approval_modal = false;
        $this->selected_request = null;
        $this->approval_notes = '';
    }

    public function approveRequest()
    {
        if (!$this->selected_request) {
            return;
        }

        DB::transaction(function () {
            // Update the payment request status
            $this->selected_request->update([
                'status' => 'approved',
                'notes' => $this->approval_notes,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Add amount to agent's wallet
            $agent = $this->selected_request->agent;
            $agent->wallet += $this->selected_request->amount;
            $agent->save();

            // Update order payment status to paid
            $order = $this->selected_request->order;
            $order->update([
                'payment_status' => \App\Enum\PaymentStatus::Paid,
                'status' => \App\Enum\OrderStatus::Confirmed,
            ]);
        });

        $this->closeApprovalModal();
        $this->dispatch('request-approved');
    }

    public function rejectRequest()
    {
        if (!$this->selected_request) {
            return;
        }

        $this->selected_request->update([
            'status' => 'rejected',
            'notes' => $this->approval_notes,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->closeApprovalModal();
        $this->dispatch('request-rejected');
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->status_filter = '';
        $this->resetPage();
    }
}
