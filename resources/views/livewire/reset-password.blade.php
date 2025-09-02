<?php

use Carbon\Carbon;
use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Hash;

new class extends Component {
    use Toast;

    // Form fields and state tracker
    public $email;
    public $otp;
    public $password;
    public $password_confirmation;
    public $step = 1;

    /**
     * Verify the submitted 6-digit OTP.
     *
     * @return void
     */
    public function verifyOtp(): void
    {
        // Step 1: Validate OTP format
        $this->validate([
            'otp' => 'required|digits:6',
        ]);

        // Step 2: Retrieve email from session
        $this->email = session('reset_email');

        if (empty($this->email)) {
            $this->error('Email session is missing');
            return;
        }

        // Step 3: Find the user by email
        $user = User::where('email', $this->email)->first();

        if (!$user) {
            $this->error('User not found');
            return;
        }

        // Step 4: Check if OTP matches and hasn't expired
        if (!Hash::check($this->otp, $user->otp)) {
            $this->error('Invalid OTP');
            return;
        }

        if (Carbon::parse($user->otp_expires_at)->isPast()) {
            $this->error('OTP has expired');
            return;
        }

        // Step 5: Proceed to password reset step
        $this->success('OTP Verified! Enter New Password.');
        $this->step = 2;
    }

    /**
     * Reset the user's password after successful OTP verification.
     *
     * @return void
     */
    public function resetPassword(): void
    {
        // Step 1: Validate new password and confirmation
        $this->validate([
            'password' => 'required|confirmed',
        ]);

        // Step 2: Retrieve the user again using the session-stored email
        $user = User::where('email', $this->email)->first();

        // Step 3: Update the password and clear OTP info
        $user->update([
            'password' => Hash::make($this->password),
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        // Step 4: Clear session and redirect with success
        session()->forget('reset_email');

        $this->success('Password reset successfully', redirectTo: '/');
    }
}; ?>

<div>
    <div class="flex items-center justify-center h-screen">
        <x-card class="w-8/12">
            <x-header title="Reset Password" size="text-xl font-bold" separator class="bg-white px-2 pt-2" />
            @if ($step === 1)
                <x-form wire:submit="verifyOtp">
                    <div class="flex gap-4">
                        <span>Enter OTP:</span>
                        <x-pin wire:model="otp" size="6" numeric />
                    </div>
                    <x-slot:actions>
                        <x-button type="submit" class="btn-primary btn-sm" label="Verify OTP" spinner="verifyOtp" />
                    </x-slot:actions>
                </x-form>
            @endif
            @if ($step === 2)
                <x-form wire:submit="resetPassword">
                    <div class="grid grid-cols-2 gap-4">
                        <x-password wire:model="password" label="New Password" placeholder="Enter Password" required
                            right />
                        <x-password wire:model="password_confirmation" label="Confirm Password"
                            placeholder="Confirm Password" required right />
                    </div>
                    <x-slot:actions>
                        <x-button type="submit" class="btn-primary btn-sm" label="Reset Password"
                            spinner="resetPassword" />
                    </x-slot:actions>
                </x-form>
            @endif
        </x-card>
    </div>
</div>
