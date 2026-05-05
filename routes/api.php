<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

use App\Http\Controllers\WebrtcController;

Route::post('/webrtc/viewer-join', [WebrtcController::class, 'viewerJoin']);
Route::post('/webrtc/offer', [WebrtcController::class, 'offer']);
Route::post('/webrtc/answer', [WebrtcController::class, 'answer']);
Route::post('/upload-capture', [App\Http\Controllers\CaptureUploadController::class, 'upload']);
Route::get('/captures/latest', [App\Http\Controllers\CaptureUploadController::class, 'latest']);
Route::post('/trigger-capture', [App\Http\Controllers\CaptureTriggerController::class, 'trigger']);
Route::post('/upload-video', [App\Http\Controllers\VideoUploadController::class, 'upload']);
Route::get('/videos/latest', [App\Http\Controllers\VideoUploadController::class, 'latest']);
Route::post('/upload-video/chunk', [App\Http\Controllers\VideoChunkController::class, 'chunk']);
Route::post('/upload-video/assemble', [App\Http\Controllers\VideoChunkController::class, 'assemble']);

// New: Resumable chunked upload using laravel-chunk-upload package
Route::post('/upload-video/chunked', [App\Http\Controllers\ChunkedVideoUploadController::class, 'upload']);

// App settings — readable by the player page, writable by the Tauri app
Route::get('/settings',  [App\Http\Controllers\AppSettingsController::class, 'show']);
Route::post('/settings', [App\Http\Controllers\AppSettingsController::class, 'update']);

// Monitoring broadcast endpoint — receives status events from the Tauri Camera Controls app
// and re-broadcasts them on the camera-control Pusher channel so the /monitoring page can receive them.
Route::post('/monitor/broadcast', function (\Illuminate\Http\Request $request) {
    $event = $request->input('event');
    $data  = $request->input('data', []);

    if (empty($event) || !is_string($event)) {
        return response()->json(['error' => 'event is required'], 422);
    }

    // Sanitise event name: only alphanumeric, underscores, colons, hyphens
    if (!preg_match('/^[\w:\-]{1,80}$/', $event)) {
        return response()->json(['error' => 'invalid event name'], 422);
    }

    // Persist status events in cache so the monitoring page can hydrate on reload
    $state = \Illuminate\Support\Facades\Cache::get('monitor_state', []);
    $data  = is_array($data) ? $data : [];
    switch ($event) {
        case 'camera_connected':
            $state['camera']        = 'connected';
            $state['camera_name']   = $data['name'] ?? '';
            $state['camera_source'] = $data['source'] ?? 'digicamcontrol';
            break;
        case 'camera_disconnected':
            $state['camera']        = 'disconnected';
            $state['camera_name']   = '';
            $state['camera_source'] = $data['source'] ?? 'digicamcontrol';
            break;
        case 'obs_connected':
            $state['obs']       = 'connected';
            $state['obs_scene'] = $data['scene'] ?? '';
            break;
        case 'obs_disconnected':
            $state['obs']       = 'disconnected';
            $state['obs_scene'] = '';
            break;
        case 'feed_started':
            $state['feed']        = 'started';
            $state['feed_device'] = $data['device'] ?? '';
            break;
        case 'feed_stopped':
            $state['feed']        = 'stopped';
            $state['feed_device'] = '';
            break;
        case 'recording_started':
            $state['recording'] = 'started';
            break;
        case 'recording_stopped':
            $state['recording'] = 'stopped';
            break;
        case 'audio_configured':
            $state['audio_source']       = $data['source'] ?? '';
            $state['audio_last_trigger'] = null;
            break;
        case 'audio_source_ok':
            $state['audio_source']       = $data['source'] ?? $state['audio_source'] ?? '';
            $state['audio_last_trigger'] = 'ok';
            break;
        case 'audio_source_fail':
            $state['audio_source']       = $data['source'] ?? $state['audio_source'] ?? '';
            $state['audio_last_trigger'] = 'fail';
            $state['audio_last_error']   = $data['error'] ?? '';
            break;
        case 'audio_source_none':
            $state['audio_last_trigger'] = 'none';
            break;
        case 'setting_update':
            // Persist app settings to DB (e.g. recording_duration_sec from Tauri app)
            if (!empty($data) && is_array($data)) {
                foreach ($data as $key => $value) {
                    // Only allow safe alphanumeric keys
                    if (preg_match('/^[a-z0-9_]{1,64}$/', (string) $key)) {
                        \App\Models\AppSetting::set((string) $key, $value);
                    }
                }
            }
            break;
    }
    $state['updated_at'] = now()->toIso8601String();
    \Illuminate\Support\Facades\Cache::put('monitor_state', $state, now()->addHours(12));

    try {
        \Illuminate\Support\Facades\Broadcast::channel('camera-control', fn() => true);
        $pusher = new \Pusher\Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            [
                'cluster'   => config('broadcasting.connections.pusher.options.cluster', 'mt1'),
                'useTLS'    => true,
            ]
        );
        $pusher->trigger('camera-control', $event, $data);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }

    return response()->json(['ok' => true, 'event' => $event]);
});

// Monitoring status endpoint
Route::get('/monitor/status', function () {
    $disk = \Illuminate\Support\Facades\Storage::disk('public');

    // Latest photo
    $photos = $disk->files('captures');
    usort($photos, fn($a, $b) => $disk->lastModified($b) <=> $disk->lastModified($a));
    $latestPhoto = $photos[0] ?? null;
    $latestPhotoTs = $latestPhoto ? $disk->lastModified($latestPhoto) : null;

    // Latest video
    $videos = $disk->files('videos');
    usort($videos, fn($a, $b) => $disk->lastModified($b) <=> $disk->lastModified($a));
    $latestVideo = $videos[0] ?? null;
    $latestVideoTs = $latestVideo ? $disk->lastModified($latestVideo) : null;

    // Pusher config present
    $pusherConfigured = !empty(config('broadcasting.connections.pusher.key'));

    return response()->json([
        'server_time'       => now()->toIso8601String(),
        'pusher_configured' => $pusherConfigured,
        'monitor_state'     => \Illuminate\Support\Facades\Cache::get('monitor_state', []),
        'photos' => [
            'count'     => count($photos),
            'latest_ts' => $latestPhotoTs,
            'latest_url'=> $latestPhoto ? $disk->url($latestPhoto) : null,
        ],
        'videos' => [
            'count'     => count($videos),
            'latest_ts' => $latestVideoTs,
            'latest_url'=> $latestVideo ? $disk->url($latestVideo) : null,
        ],
    ]);
});
