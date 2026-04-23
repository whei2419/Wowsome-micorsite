<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\TriggerCapture;

class CaptureTriggerController extends Controller
{
    public function trigger(Request $request)
    {
        $user = $request->user();
        $by = $user ? $user->id : null;

        $mode = strtolower((string) $request->input('mode', 'photo'));
        if (!in_array($mode, ['photo', 'video'], true)) {
            $mode = 'photo';
        }

        $durationSec = (int) $request->input('durationSec', 20);
        $durationSec = max(3, min(30, $durationSec));

        event(new TriggerCapture($by, $mode, $durationSec));

        return response()->json([
            'status' => 'ok',
            'mode' => $mode,
            'durationSec' => $durationSec,
        ]);
    }
}
