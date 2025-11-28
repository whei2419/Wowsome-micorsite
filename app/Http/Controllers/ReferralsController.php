<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ReferralsController extends Controller
{
    //
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // Ensure user has a referral code
        if (empty($user->referral_code)) {
            $user->referral_code = User::generateReferralCode($user->id);
            $user->save();
        }

        $totalReferrals = $user->referrals()->count();

        // Count referrals who claimed either station 1 OR station 2
        // Each referral who claimed at least one of these stations counts as 1 successful referral
        $completedReferrals = $user->referrals()
            ->whereHas('stationUser', function($query) {
                $query->whereIn('station_id', [1, 2]);
            })
            ->count();

        $referralUrl = url('/register?referral=' . $user->referral_code);

        return view('referrals', compact('user', 'totalReferrals', 'completedReferrals', 'referralUrl'));
    }

}
