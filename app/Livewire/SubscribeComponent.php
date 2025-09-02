<?php

namespace App\Livewire;

use App\Models\Subscriber;
use Livewire\Component;
use Mary\Traits\Toast;

class SubscribeComponent extends Component
{
    use Toast;
    public $email;
    public function render()
    {
        return view('livewire.subscribe-component');
    }
    public function storeEmail()
    {
        Subscriber::create([
            'email' => $this->email,
        ]);
        $this->reset('email');
        $this->success('Subscribe successfull');
    }
}
