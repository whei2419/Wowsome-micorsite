<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StationController extends Controller
{
    //
    public function dashboard()
    {
        $userId = Auth::id();

        $user = User::with('stationUser')->where('id', $userId)->first();

        $stationDone = $user->stationUser->count();
        $stations = Station::get();

        $completedStationIds = $user->stationUser->pluck('id')->toArray();

        // Add status flag to each station
        foreach ($stations as $station) {
            $station->status = $user->stationUser->contains('station_id', $station->id);
        }

        // Check if user has completed referrals for station 3 access
        $hasCompletedReferrals = $user->hasCompletedReferrals();

        $nextStation = $stations->firstWhere(function ($station) use ($user) {
            return !$user->stationUser()->where('station_id', $station->id)->exists();
        });

        return view('dashboard', compact('stations', 'stationDone', 'hasCompletedReferrals', 'completedStationIds', 'nextStation'));
    }
}
