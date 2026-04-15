<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\babyEvent; // Import the babyEvent event class

class IpadController extends Controller
{
    public function index()
    {
        return view('ipad');
    }

    public function index2()
    {
        return view('ipad.message-type-duplicate');
    }

    public function pushCoral(Request $request)
    {
        $request->validate([
            'coral_image_id' => 'required|string|max:2048', // max 2MB
        ]);

        $publicPath = asset('images/vip/' . $request->coral_image_id . '.webp'); // Generate URL for the image

        // Fire the event (use correct fields)
        broadcast(new babyEvent($publicPath, 'test', 'coral-vip', charname: 'name'))->toOthers();

        // Return success response
        return response()->json([
            'success' => true,
            'message' => 'sent successfully',
        ]);
    }

    public function playerPlay(Request $request)
    {
        $duration = (int) $request->input('duration', 60);
        broadcast(new babyEvent('', '', 'player-play', (string) $duration))->toOthers();
        return response()->json(['success' => true]);
    }

    public function playerStop()
    {
        broadcast(new babyEvent('', '', 'player-stop', ''))->toOthers();
        return response()->json(['success' => true]);
    }

    public function playerRestart(Request $request)
    {
        $duration = (int) $request->input('duration', 60);
        broadcast(new babyEvent('', '', 'player-restart', (string) $duration))->toOthers();
        return response()->json(['success' => true]);
    }
}
