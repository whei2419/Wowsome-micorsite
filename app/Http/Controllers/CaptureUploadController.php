<?php

namespace App\Http\Controllers;

use App\Events\CaptureUploaded;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;

class CaptureUploadController extends BaseController
{
    public function upload(Request $request)
    {
        // accept multipart file under 'file' or 'image', or base64 string under 'image'
        if ($request->hasFile('file') || $request->hasFile('image')) {
            \Log::info('CaptureUpload: multipart file received');
            $path = ($request->file('file') ?? $request->file('image'))->store('captures', 'public');
            if (! $path) {
                \Log::error('CaptureUpload: file store failed');

                return response()->json(['error' => 'file_store_failed'], 500);
            }
            \Log::info('CaptureUpload: file stored', ['path' => $path]);
        } elseif ($request->filled('image')) {
            \Log::info('CaptureUpload: base64 image received');
            $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $request->input('image'));
            $decoded = base64_decode($base64, strict: true);
            if ($decoded === false) {
                \Log::error('CaptureUpload: base64 decode failed');

                return response()->json(['error' => 'invalid_base64'], 422);
            }
            $path = 'captures/'.uniqid('cap_').'.png';
            Storage::disk('public')->put($path, $decoded);
            \Log::info('CaptureUpload: base64 image stored', ['path' => $path]);
        } else {
            \Log::warning('CaptureUpload: no image provided in request');

            return response()->json(['error' => 'no_image_provided'], 422);
        }

        $url = Storage::disk('public')->url($path);
        event(new CaptureUploaded($url));

        // log the upload success and error cases
        \Log::info('Capture uploaded', ['path' => $path, 'url' => $url]);

        return response()->json(['ok' => true, 'path' => $path, 'url' => $url]);
    }

    public function latest()
    {
        $files = Storage::disk('public')->files('captures');
        if (empty($files)) {
            return response()->json(['ok' => false, 'message' => 'no captures'], 404);
        }
        // get last modified by checking timestamps
        usort($files, function ($a, $b) {
            return Storage::disk('public')->lastModified($b) <=> Storage::disk('public')->lastModified($a);
        });
        $path = $files[0];
        $url = Storage::disk('public')->url($path);

        return response()->json(['ok' => true, 'path' => $path, 'url' => $url]);
    }
}
