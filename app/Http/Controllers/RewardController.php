<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StationUser;

class RewardController extends Controller
{
    //
    public function index($stationId)
    {
        $user = Auth::user();
        $userHash = hash('sha256', $user->id);

        // Check if user has already claimed this reward
        $hasClaimed = StationUser::where('user_id', $user->id)
            ->where('station_id', $stationId)
            ->exists();

        return view('rewards', [
            'user' => $user,
            'userHash' => $userHash,
            'hasClaimed' => $hasClaimed,
            'stationId' => $stationId
        ]);
    }
}
