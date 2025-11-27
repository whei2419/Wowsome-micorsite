<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Station;
use App\Models\StationUser;

class ConciergeController extends Controller
{
    //
    public function index()
    {
        $stations = Station::all();
        return view('conciergeScanner', compact('stations'));
    }

    public function searchUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'User not found'
            ], 404);
        }

        return $this->getUserData($user);
    }

    public function searchUserByHash(Request $request)
    {
        $request->validate([
            'hash' => 'required|string'
        ]);

        // Find user by matching hash
        $users = User::all();
        $user = null;

        foreach ($users as $u) {
            if (hash('sha256', $u->id) === $request->hash) {
                $user = $u;
                break;
            }
        }

        if (!$user) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Invalid QR code'
            ], 404);
        }

        return $this->getUserData($user);
    }

    private function getUserData($user)
    {
        // Check if user has completed referrals (for Station 3 eligibility)
        $hasCompletedReferrals = $user->hasCompletedReferrals();

        // Get referral counts for debugging
        $totalReferrals = $user->referrals()->count();

        // Get stations already claimed by this user
        $claimedStationIds = $user->stationUser()->pluck('station_id')->toArray();

        Log::info('User search for rewards', [
            'user_id' => $user->id,
            'email' => $user->email,
            'total_referrals' => $totalReferrals,
            'hasCompletedReferrals' => $hasCompletedReferrals,
            'claimed_stations' => $claimedStationIds
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User found',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'userHash' => hash('sha256', $user->id),
                'hasCompletedReferrals' => $hasCompletedReferrals,
                'totalReferrals' => $totalReferrals,
                'claimedStationIds' => $claimedStationIds
            ]
        ]);
    }

    public function claimReward(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'station_id' => 'required|exists:stations,id'
        ]);

        $userId = $request->user_id;
        $stationId = $request->station_id;

        // Check if user already claimed this reward
        $existingClaim = StationUser::where('user_id', $userId)
            ->where('station_id', $stationId)
            ->first();

        if ($existingClaim) {
            return response()->json([
                'status' => 'already_claimed',
                'message' => 'User has already claimed this reward'
            ], 400);
        }

        // Create the station user record
        StationUser::create([
            'user_id' => $userId,
            'station_id' => $stationId
        ]);

        Log::info('Reward claimed', [
            'user_id' => $userId,
            'station_id' => $stationId
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Reward claimed successfully'
        ]);
    }
}
