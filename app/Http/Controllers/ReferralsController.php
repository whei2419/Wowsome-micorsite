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

        // Count referrals who completed both station 1 and 2
        $completedReferrals = $user->referrals()
            ->whereHas('stationUser', function($query) {
                $query->whereIn('station_id', [1, 2]);
            })
            ->withCount(['stationUser' => function($query) {
                $query->whereIn('station_id', [1, 2]);
            }])
            ->get()
            ->filter(function($referral) {
                return $referral->station_user_count >= 2;
            })
            ->count();

        $referralUrl = url('/register?referral=' . $user->referral_code);

        return view('referrals', compact('user', 'totalReferrals', 'completedReferrals', 'referralUrl'));
    }

}
