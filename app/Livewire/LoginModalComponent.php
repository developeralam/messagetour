<?php

namespace App\Livewire;

use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Component;
use App\Enum\UserStatus;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginModalComponent extends Component
{
    use Toast;

    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required')]
    public string $password = '';

    public $show = false;

    public $redirectUrl = '';

    #[On('showLoginModal')]
    public function showLoginModal(string $url = '')
    {
        $this->redirectUrl = $url;  // Set the redirect URL from the button click
        $this->show = true;
    }

    public function login()
    {
        $this->validate();
        try {
            // Manually find user
            $user = User::where('email', $this->email)->first();

            if (! $user || !Hash::check($this->password, $user->password)) {
                $this->error('Invalid credentials');
                return;
            }

            // Check if user is blocked (status = 2)
            if ($user->status == UserStatus::Inactive) {
                $this->error('You cannot login now.');
                $this->show = false;
                return;
            }

            // All good, log in the user
            Auth::login($user);
            request()->session()->regenerate();

            $this->success('Login Successful');
            $this->show = false;

            return redirect()->to($this->redirectUrl);
        } catch (\Throwable $th) {
            $this->error('Invalid credentials');
            $this->show = false;
        }
    }

    public function render()
    {
        return view('livewire.login-modal-component');
    }
}
