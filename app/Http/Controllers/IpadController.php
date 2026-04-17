<?php

namespace App\Http\Controllers;

use App\Events\babyEvent;
use Illuminate\Http\Request; // Import the babyEvent event class

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

        $publicPath = asset('images/vip/'.$request->coral_image_id.'.webp'); // Generate URL for the image

        // Fire the event (use correct fields)
        broadcast(new babyEvent($publicPath, 'test', 'coral-vip', charname: 'name'))->toOthers();

        // Return success response
        return response()->json([
            'success' => true,
            'message' => 'sent successfully',
        ]);
    }

    /**
     * Called by the Windows app to notify the web player of a state change.
     * e.g. GET /player/callback?type=player-ended
     */
    public function playerCallback(Request $request)
    {
        $type = $request->input('type', '');
        // Broadcast to ALL subscribers (no toOthers — Windows app has no socket ID)
        broadcast(new babyEvent('', '', $type, ''));

        return response()->json(['success' => true, 'type' => $type]);
    }

    public function playerPing()
    {
        broadcast(new babyEvent('', '', 'player-ping', ''))->toOthers();

        return response()->json(['success' => true, 'message' => 'pong']);
    }

    public function playerPlay(Request $request)
    {
        $duration = (int) $request->input('duration', 10);
        broadcast(new babyEvent('', '', 'player-play', (string) $duration))->toOthers();

        return response()->json(['success' => true]);
    }

    public function playerPause()
    {
        broadcast(new babyEvent('', '', 'player-pause', ''))->toOthers();

        return response()->json(['success' => true]);
    }

    public function playerResume()
    {
        broadcast(new babyEvent('', '', 'player-resume', ''))->toOthers();

        return response()->json(['success' => true]);
    }

    public function playerRestart(Request $request)
    {
        $duration = (int) $request->input('duration', 10);
        broadcast(new babyEvent('', '', 'player-restart', (string) $duration))->toOthers();

        return response()->json(['success' => true]);
    }
}
