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
