<?php

namespace App\Livewire\Partner;

use App\Models\AgentPaymentRequest as AgentPaymentRequestModel;
use Livewire\Component;
use Livewire\WithPagination;

class AgentPaymentRequest extends Component
{
    use WithPagination;

    public $search = '';
    public $status_filter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status_filter' => ['except' => ''],
    ];

    public function render()
    {
        $agent = auth()->user()->agent;

        if (!$agent) {
            return view('livewire.partner.agent-payment-request', [
                'requests' => collect()
            ]);
        }

        $requests = AgentPaymentRequestModel::with(['order', 'approvedBy'])
            ->where('agent_id', $agent->id)
            ->when($this->search, function ($query) {
                $query->whereHas('order', function ($q) {
                    $q->where('tran_id', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status_filter, function ($query) {
                $query->where('status', $this->status_filter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.partner.agent-payment-request', [
            'requests' => $requests
        ]);
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->status_filter = '';
        $this->resetPage();
    }
}
