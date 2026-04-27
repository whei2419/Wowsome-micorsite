<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use App\Events\VideoUploaded;

class VideoUploadController extends BaseController
{
    public function upload(Request $request)
    {
        // Accept multipart video file under 'file' or 'video'
        if ($request->hasFile('file') || $request->hasFile('video')) {
            $file = $request->file('file') ?: $request->file('video');

            // Validate by extension — MIME detection is unreliable for MKV/MOV/AVI
            $allowedExts = ['mp4', 'mov', 'webm', 'mkv', 'avi', 'mts', 'm2ts', 'wmv'];
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, $allowedExts)) {
                return response()->json(['error' => 'invalid_video_type', 'ext' => $ext], 422);
            }

            $path = $file->store('videos', 'public');
        } else {
            return response()->json(['error' => 'no_video_provided'], 422);
        }

        $url = Storage::disk('public')->url($path);
        event(new VideoUploaded($url));
        return response()->json(['ok' => true, 'path' => $path, 'url' => $url]);
    }

    public function latest()
    {
        $files = Storage::disk('public')->files('videos');
        if (empty($files)) {
            return response()->json(['ok' => false, 'message' => 'no videos'], 404);
        }
        usort($files, function ($a, $b) {
            return Storage::disk('public')->lastModified($b) <=> Storage::disk('public')->lastModified($a);
        });
        $path = $files[0];
        $url = Storage::disk('public')->url($path);
        return response()->json(['ok' => true, 'path' => $path, 'url' => $url]);
    }
}
