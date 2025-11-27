<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Countries;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Events\Registered;
use App\Providers\RouteServiceProvider;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        $referralCode = $request->query('referral');
        return view('auth.register', compact('referralCode'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'fname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'country' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (User::where('number', $value)->exists()) {
                      $fail('This phone number is already registered. If you’ve signed up for a previous event or pre-registered, please. <a href="' . route('login') . '">Login</a> instead');
                    }
                }
            ],

        ]);
        $marketing = false;

        if($request->has('marketing')){
            $marketing = true;
        }

        // After validation, fetch country by phone number
        $phoneNumber = $request->input('country');

      // Extract the phone prefix
        $phonePrefix = '+' . substr($phoneNumber, 1, 2); // This assumes the prefix is always 2 characters after the '+'

        // Query the country based on the phone prefix
        $country = Countries::where('phone_code', $phonePrefix)->first();
        $otp = rand(100000, 999999);

        // Handle referral code
        $referredBy = null;
        $referralCode = $request->input('referral_code') ?? $request->input('referral');

        Log::info('Registration attempt', [
            'all_inputs' => $request->all(),
            'referral_from_form' => $request->input('referral'),
            'referral_code_from_form' => $request->input('referral_code'),
            'final_referral_code' => $referralCode
        ]);

        if (!empty($referralCode)) {
            // Find user by referral code
            try {
                $referrerId = User::getUserIdFromReferralCode($referralCode);
                Log::info('Referral code received', [
                    'original' => $referralCode,
                    'decoded_user_id' => $referrerId
                ]);

                if ($referrerId) {
                    $referrer = User::find($referrerId);
                    if ($referrer) {
                        $referredBy = $referrer->id;
                        Log::info('Referrer found', ['referrer_id' => $referredBy, 'referrer' => $referrer]);
                    } else {
                        Log::warning('Referrer not found', ['referrer_id' => $referrerId]);
                    }
                } else {
                    Log::warning('Invalid referral code', ['code' => $referralCode]);
                }
            } catch (\Exception $e) {
                Log::error('Referral code error', ['error' => $e->getMessage()]);
                // Invalid referral code, continue without referral
            }
        } else {
            Log::warning('No referral code provided');
        }

        Log::info('About to create user', ['referred_by' => $referredBy]);

        $user = User::create([
            'name' => $request->fname,
            'number' => $phoneNumber,
            'email' => $request->email,
            'country'=> $country->name,
            'referred_by' => $referredBy,
            'last_login_at' => Carbon::now(),
            'password' => Hash::make('password'),
        ]);

        Log::info('User created', [
            'user_id' => $user->id,
            'referred_by_in_db' => $user->referred_by,
            'all_user_data' => $user->toArray()
        ]);

        $user->assignRole('client');
        // $request->session()->flash('showWelcomeModal', true);
        // Use the insert method to insert multiple records in one query
        event(new Registered($user));
        // GlobalHelper::sendOtpSms($phoneNumber, $otp);

        Auth::login($user);

    //     // return redirect(RouteServiceProvider::HOME);
        return redirect()->route('dashboard');
    }
}
