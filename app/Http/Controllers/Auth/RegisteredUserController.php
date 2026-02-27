<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Countries;
use App\Models\Regime;
use App\Models\RegimeUser;

use Carbon\Carbon;
use App\Helpers\GlobalHelper;

use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Rules\InternationalPhoneNumber;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'agree' => ['accepted'],
        ]);
        $marketing = false;

        if($request->has('marketing')){
            $marketing = true;
        }

        $user = User::create([
            'fname' => $request->fname,
            'email' => $request->email,
            'marketing' => $marketing,
            'last_login_at' => Carbon::now(),
            'password' => Hash::make('password'),
        ]);

        $user->assignRole('client');
        // $request->session()->flash('showWelcomeModal', true);
        // Use the insert method to insert multiple records in one query
        event(new Registered($user));
        // GlobalHelper::sendOtpSms($phoneNumber, $otp);

        Auth::login($user);

        // Redirect to the post-registration welcome page
        return redirect()->route('register.welcome');
    }
}
