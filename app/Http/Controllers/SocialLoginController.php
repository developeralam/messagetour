<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Agent;
use App\Enum\UserType;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    /**
     * Redirect the user to the OAuth provider's authentication page.
     *
     * This method stores the type of login (customer or partner) in the session,
     * then redirects the user to the selected provider's OAuth authentication page.
     *
     * @param  string  $provider  The name of the social provider (e.g., linkedin, google, facebook).
     * @return RedirectResponse
     */
    public function redirectToProvider(string $provider): RedirectResponse
    {
        // Store login type in session (default: customer)
        session(['login_type' => request('type', 'customer')]);

        // Redirect to provider's OAuth page
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the OAuth provider callback.
     *
     * After the provider redirects back, this method processes the authenticated user,
     * creates or updates the user record, assigns the proper role (Customer or Agent),
     * logs the user in, and redirects to the appropriate location.
     *
     * @param  string  $provider  The name of the social provider (e.g., linkedin, google, facebook).
     * @return RedirectResponse
     */
    public function handleProviderCallback(string $provider): RedirectResponse
    {
        // Retrieve the login type from session
        $type = session('login_type', 'customer');

        // Determine the user type and redirect route based on login type
        $userType = match ($type) {
            'partner' => UserType::Agent,
            default => UserType::Customer,
        };

        $redirectRoute = match ($type) {
            'partner' => '/partner/login',
            default => '/customer/login',
        };

        try {
            // Get user info from the social provider
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            // Redirect back if there's an error during social login
            return redirect($redirectRoute)->withErrors(['msg' => 'Login failed']);
        }

        // Create or update the User
        $user = User::updateOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Unknown',
                'password' => Hash::make('12345678'), // Default password (can be replaced)
                'email_verified_at' => now(),
                'type' => $userType,
            ]
        );

        // Ensure corresponding Customer or Agent exists
        match ($userType) {
            UserType::Customer => Customer::updateOrCreate(['user_id' => $user->id]),
            UserType::Agent => Agent::updateOrCreate(['user_id' => $user->id]),
        };

        // Log in the user and clear session state
        Auth::login($user);
        session()->forget('login_type');

        // Redirect to originally intended page
        return redirect()->intended('/');
    }
}
