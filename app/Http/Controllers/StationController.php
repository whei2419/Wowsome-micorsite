<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Upload;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        // Check if user has completed referrals for station 3 & 4 access
        $completedReferralsCount = $user->getCompletedReferralsCount();
        $hasCompletedReferrals = $completedReferralsCount >= 1; // Tier 1
        $hasTier2Referrals = $completedReferralsCount >= 5; // Tier 2

        $nextStation = $stations->firstWhere(function ($station) use ($user) {
            return !$user->stationUser()->where('station_id', $station->id)->exists();
        });

        return view('dashboard', compact('stations', 'stationDone', 'hasCompletedReferrals', 'hasTier2Referrals', 'completedReferralsCount', 'completedStationIds', 'nextStation'));
    }


    public function show($id)
    {
        // Fetch upload record
        $upload = Upload::findOrFail($id);

        return view('lantern', [
            // Public preview URL
            'imageUrl' => Storage::disk('public')->url($upload->image_path),

            // Forced download URL
            'downloadUrl' => route('lantern.download', $upload->id),

            // Date & time
            'date' => Carbon::parse($upload->created_at)->format('d-m-Y'),
            'time' => Carbon::parse($upload->created_at)->format('H:i:s'),
        ]);
    }

    public function download($id)
    {
        $upload = Upload::findOrFail($id);

        // Security check (optional but recommended)
        if (!Storage::disk('public')->exists($upload->image_path)) {
            abort(404, 'File not found.');
        }

        // Force download with original filename
        return Storage::disk('public')->download(
            $upload->image_path,
            'wishing-lantern-' . $upload->id . '.' . pathinfo($upload->image_path, PATHINFO_EXTENSION)
        );
    }

}
