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
        event(new TriggerCapture($by));
        return response()->json(['status' => 'ok']);
    }
}
