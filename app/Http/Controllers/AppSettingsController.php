<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;

class AppSettingsController extends Controller
{
    const KEY_DURATION = 'recording_duration_sec';
    const DEFAULT_DURATION = 20;
    const MIN_DURATION = 3;
    const MAX_DURATION = 600;

    public function show()
    {
        $duration = (int) AppSetting::get(self::KEY_DURATION, self::DEFAULT_DURATION);

        return response()->json([
            'recordingDurationSec' => $duration,
        ]);
    }

    public function update(Request $request)
    {
        $raw = (int) $request->input('recordingDurationSec', self::DEFAULT_DURATION);
        $duration = max(self::MIN_DURATION, min(self::MAX_DURATION, $raw));

        AppSetting::set(self::KEY_DURATION, $duration);

        return response()->json([
            'recordingDurationSec' => $duration,
        ]);
    }
}
