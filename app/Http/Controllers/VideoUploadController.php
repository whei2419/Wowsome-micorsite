<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Events\VideoUploaded;

class VideoUploadController extends BaseController
{
    public function upload(Request $request)
    {
        // Log request details
        Log::info('VideoUpload: request headers', [
            'content_length_header' => $request->header('Content-Length'),
            'server_CONTENT_LENGTH' => $_SERVER['CONTENT_LENGTH'] ?? '(not set)',
            'server_CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? '(not set)',
            'content_type' => $request->header('Content-Type'),
            'http_version' => $_SERVER['SERVER_PROTOCOL'] ?? '(unknown)',
            'all_headers' => $request->headers->all(),
        ]);

        Log::info('VideoUpload: request received', [
            'method' => $request->method(),
            'ip' => $request->ip(),
            'has_file_file' => $request->hasFile('file'),
            'has_file_video' => $request->hasFile('video'),
            'all_files' => array_keys($request->allFiles()),
            'all_inputs' => array_keys($request->all()),
            'files_superglobal_count' => count($_FILES),
            'raw_FILES' => $_FILES,
        ]);

        // Accept multipart video file under 'file' or 'video'
        if ($request->hasFile('file') || $request->hasFile('video')) {
            $file = $request->file('file') ?: $request->file('video');

            // Validate by extension — MIME detection is unreliable for MKV/MOV/AVI
            $allowedExts = ['mp4', 'mov', 'webm', 'mkv', 'avi', 'mts', 'm2ts', 'wmv'];
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, $allowedExts)) {
                Log::warning('VideoUpload: invalid video type', [
                    'ext' => $ext,
                    'original_name' => $file->getClientOriginalName(),
                ]);
                return response()->json(['error' => 'invalid_video_type', 'ext' => $ext], 422);
            }

            $path = $file->store('videos', 'public');
            
            Log::info('VideoUpload: file stored', [
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'size_bytes' => $file->getSize(),
            ]);
        } else {
            Log::warning('VideoUpload: no video provided', [
                'content_type' => $request->header('Content-Type'),
                'content_length' => $request->header('Content-Length'),
                'all_files' => array_keys($request->allFiles()),
                'all_inputs' => array_keys($request->all()),
            ]);
            return response()->json(['error' => 'no_video_provided'], 422);
        }

        $url = Storage::disk('public')->url($path);
        event(new VideoUploaded($url));
        
        Log::info('VideoUpload: success', ['path' => $path, 'url' => $url]);
        
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
