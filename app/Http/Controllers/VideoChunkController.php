<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use App\Events\VideoUploaded;

class VideoChunkController extends BaseController
{
    /**
     * Receive a single chunk and persist it to local temp storage.
     * POST /api/upload-video/chunk
     */
    public function chunk(Request $request)
    {

        $uploadId   = $request->input('upload_id', '');
        $chunkIndex = (int) $request->input('chunk_index', -1);
        $filename   = basename((string) $request->input('filename', ''));

        // Strict upload_id to prevent path traversal
        if (!preg_match('/^[a-zA-Z0-9_\-]{1,200}$/', $uploadId)) {
            return response()->json(['error' => 'invalid_upload_id'], 422);
        }

        if ($chunkIndex < 0 || !$request->hasFile('file') || !$filename) {
            return response()->json(['error' => 'missing_fields'], 422);
        }

        // Store raw chunk bytes in private local storage (never public)
        $request->file('file')->storeAs(
            'chunks/' . $uploadId,
            (string) $chunkIndex,
            'local'
        );

        return response()->json(['ok' => true, 'chunk' => $chunkIndex]);
    }

    /**
     * Assemble all chunks into a final video file, fire VideoUploaded event.
     * POST /api/upload-video/assemble
     */
    public function assemble(Request $request)
    {
        $uploadId    = $request->input('upload_id', '');
        $totalChunks = (int) $request->input('total_chunks', 0);
        $filename    = basename((string) $request->input('filename', ''));

        if (!preg_match('/^[a-zA-Z0-9_\-]{1,200}$/', $uploadId)) {
            return response()->json(['error' => 'invalid_upload_id'], 422);
        }

        if (!$totalChunks || !$filename) {
            return response()->json(['error' => 'missing_fields'], 422);
        }

        // Validate extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowedExts = ['mp4', 'mov', 'webm', 'mkv', 'avi', 'mts', 'm2ts', 'wmv'];
        if (!in_array($ext, $allowedExts)) {
            return response()->json(['error' => 'invalid_video_type', 'ext' => $ext], 422);
        }

        $chunkDir  = storage_path('app/chunks/' . $uploadId);
        $outName   = 'videos/' . uniqid('vid_') . '.' . $ext;
        $outPath   = storage_path('app/public/' . $outName);

        // Ensure output directory exists
        $outDir = dirname($outPath);
        if (!is_dir($outDir)) {
            mkdir($outDir, 0755, true);
        }

        $out = fopen($outPath, 'wb');
        if (!$out) {
            return response()->json(['error' => 'cannot_create_output'], 500);
        }

        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkFile = $chunkDir . '/' . $i;
            if (!file_exists($chunkFile)) {
                fclose($out);
                return response()->json(['error' => 'missing_chunk', 'chunk' => $i], 422);
            }
            $in = fopen($chunkFile, 'rb');
            stream_copy_to_stream($in, $out);
            fclose($in);
        }

        fclose($out);

        // Clean up temp chunks
        foreach (glob($chunkDir . '/*') as $f) {
            @unlink($f);
        }
        @rmdir($chunkDir);

        $url = Storage::disk('public')->url($outName);
        event(new VideoUploaded($url));

        return response()->json(['ok' => true, 'path' => $outName, 'url' => $url]);
    }
}
