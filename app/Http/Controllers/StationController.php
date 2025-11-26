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

        // Determine if stations 1-4 are all completed
        $canAccessStation3 = $stations->filter(fn($s) => $s->id <= 2)->every(fn($s) => $s->status == true);

        $nextStation = $stations->firstWhere(function ($station) use ($user) {
            return !$user->stationUser()->where('station_id', $station->id)->exists();
        });

        return view('dashboard', compact('stations', 'stationDone', 'canAccessStation3', 'completedStationIds', 'nextStation'));
    }
}
