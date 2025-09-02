<?php

use Carbon\Carbon;
use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use App\Mail\ResetPasswordOtpMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

new class extends Component {
    use Toast;

    public $email;

    /**
     * Sends a 6-digit OTP to the user's email for password reset.
     * Validates email, generates OTP, stores it securely, and queues email.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendOtp()
    {
        // Step 1: Validate the email input
        $this->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Step 2: Retrieve the user by email
        $user = User::where('email', $this->email)->first();

        // Step 3: Generate a 6-digit OTP
        $otp = rand(100000, 999999);

        // Step 4: Save the hashed OTP and its expiry time
        $user->update([
            'otp' => Hash::make($otp),
            'otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // Step 5: Send the OTP via email
        Mail::to($this->email)->queue(new ResetPasswordOtpMail($otp));

        // Step 6: Store the reset email in the session
        session(['reset_email' => $this->email]);

        // Step 7: Show a success toast and redirect to OTP entry page
        $this->success('Password reset OTP sent to your email.');

        return redirect()->route('resetpassword');
    }
}; ?>

<div>
    <div class="flex items-center justify-center h-screen">
        <x-card class="p-6 shadow-md">
            <h2 class="mb-2 text-2xl font-semibold text-center text-gray-700">Forgot Password</h2>
            <x-header
                title="Enter your email address, and we'll send you a one time password (OTP) to reset your password"
                size="text-sm" />
            <x-form wire:submit="sendOtp">
                <div class="mb-4">
                    <x-input type="email" label="Email Address" wire:model="email" placeholder="E-mail Address"
                        required />
                </div>
                <x-slot:actions>
                    <x-button type="submit" label="Send OTP" class="btn-primary btn-sm" />
                </x-slot:actions>
            </x-form>
        </x-card>
    </div>
</div>
