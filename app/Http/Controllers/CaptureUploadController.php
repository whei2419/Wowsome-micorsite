<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use App\Events\CaptureUploaded;

class CaptureUploadController extends BaseController
{
    public function upload(Request $request)
    {
        // accept multipart file under 'file' or 'image', or base64 string under 'image'
        if ($request->hasFile('file') || $request->hasFile('image')) {
            $file = $request->file('file') ?: $request->file('image');
            $path = $file->store('captures', 'public');
        } elseif ($request->filled('image')) {
            $data = $request->input('image');
            // data URI or raw base64
            if (preg_match('/^data:(image\/\w+);base64,/', $data, $matches)) {
                $mime = $matches[1];
                $base64 = substr($data, strpos($data, ',') + 1);
            } else {
                // assume png if unknown
                $mime = 'image/png';
                $base64 = $data;
            }
            $decoded = base64_decode($base64);
            if ($decoded === false) {
                return response()->json(['error' => 'invalid_base64'], 422);
            }
            $ext = explode('/', $mime)[1] ?? 'png';
            $filename = 'captures/' . uniqid('cap_') . '.' . $ext;
            Storage::disk('public')->put($filename, $decoded);
            $path = $filename;
        } else {
            return response()->json(['error' => 'no_image_provided'], 422);
        }

        $url = Storage::disk('public')->url($path);
        event(new CaptureUploaded($url));
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
