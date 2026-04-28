<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Events\VideoUploaded;

class VideoChunkController extends BaseController
{
    /**
     * Receive a single chunk and persist it to local temp storage.
     * POST /api/upload-video/chunk
     */
    public function chunk(Request $request)
    {
        Log::info('VideoChunk: request received', [
            'method' => $request->method(),
            'ip' => $request->ip(),
            'upload_id' => $request->input('upload_id', '(empty)'),
            'chunk_index' => $request->input('chunk_index', '(empty)'),
            'filename' => $request->input('filename', '(empty)'),
            'has_file' => $request->hasFile('file'),
            'content_length' => $request->header('Content-Length'),
        ]);

        $uploadId   = $request->input('upload_id', '');
        $chunkIndex = (int) $request->input('chunk_index', -1);
        $filename   = basename((string) $request->input('filename', ''));

        // Strict upload_id to prevent path traversal
        if (!preg_match('/^[a-zA-Z0-9_\-]{1,200}$/', $uploadId)) {
            Log::warning('VideoChunk: invalid upload_id', ['upload_id' => $uploadId]);
            return response()->json(['error' => 'invalid_upload_id'], 422);
        }

        if ($chunkIndex < 0 || !$request->hasFile('file') || !$filename) {
            Log::warning('VideoChunk: missing fields', [
                'chunk_index' => $chunkIndex,
                'has_file' => $request->hasFile('file'),
                'filename' => $filename,
            ]);
            return response()->json(['error' => 'missing_fields'], 422);
        }

        $file = $request->file('file');
        
        Log::info('VideoChunk: storing chunk', [
            'upload_id' => $uploadId,
            'chunk_index' => $chunkIndex,
            'filename' => $filename,
            'chunk_size' => $file->getSize(),
        ]);

        // Store raw chunk bytes in private local storage (never public)
        $storedPath = $file->storeAs(
            'chunks/' . $uploadId,
            (string) $chunkIndex,
            'local'
        );

        Log::info('VideoChunk: chunk stored successfully', [
            'upload_id' => $uploadId,
            'chunk_index' => $chunkIndex,
            'stored_path' => $storedPath,
        ]);

        return response()->json(['ok' => true, 'chunk' => $chunkIndex]);
    }

    /**
     * Assemble all chunks into a final video file, fire VideoUploaded event.
     * POST /api/upload-video/assemble
     */
    public function assemble(Request $request)
    {
        Log::info('VideoAssemble: request received', [
            'method' => $request->method(),
            'ip' => $request->ip(),
            'upload_id' => $request->input('upload_id', '(empty)'),
            'total_chunks' => $request->input('total_chunks', '(empty)'),
            'filename' => $request->input('filename', '(empty)'),
        ]);

        $uploadId    = $request->input('upload_id', '');
        $totalChunks = (int) $request->input('total_chunks', 0);
        $filename    = basename((string) $request->input('filename', ''));

        if (!preg_match('/^[a-zA-Z0-9_\-]{1,200}$/', $uploadId)) {
            Log::warning('VideoAssemble: invalid upload_id', ['upload_id' => $uploadId]);
            return response()->json(['error' => 'invalid_upload_id'], 422);
        }

        if (!$totalChunks || !$filename) {
            Log::warning('VideoAssemble: missing fields', [
                'total_chunks' => $totalChunks,
                'filename' => $filename,
            ]);
            return response()->json(['error' => 'missing_fields'], 422);
        }

        // Validate extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowedExts = ['mp4', 'mov', 'webm', 'mkv', 'avi', 'mts', 'm2ts', 'wmv'];
        if (!in_array($ext, $allowedExts)) {
            Log::warning('VideoAssemble: invalid video type', [
                'filename' => $filename,
                'ext' => $ext,
            ]);
            return response()->json(['error' => 'invalid_video_type', 'ext' => $ext], 422);
        }

        $chunkDir  = storage_path('app/chunks/' . $uploadId);
        $outName   = 'videos/' . uniqid('vid_') . '.' . $ext;
        $outPath   = storage_path('app/public/' . $outName);

        Log::info('VideoAssemble: starting assembly', [
            'upload_id' => $uploadId,
            'total_chunks' => $totalChunks,
            'filename' => $filename,
            'chunk_dir' => $chunkDir,
            'output_name' => $outName,
        ]);

        // Ensure output directory exists
        $outDir = dirname($outPath);
        if (!is_dir($outDir)) {
            mkdir($outDir, 0755, true);
        }

        $out = fopen($outPath, 'wb');
        if (!$out) {
            Log::error('VideoAssemble: cannot create output file', ['output_path' => $outPath]);
            return response()->json(['error' => 'cannot_create_output'], 500);
        }

        $totalSize = 0;
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkFile = $chunkDir . '/' . $i;
            if (!file_exists($chunkFile)) {
                fclose($out);
                Log::error('VideoAssemble: missing chunk', [
                    'upload_id' => $uploadId,
                    'chunk_index' => $i,
                    'chunk_file' => $chunkFile,
                ]);
                return response()->json(['error' => 'missing_chunk', 'chunk' => $i], 422);
            }
            $chunkSize = filesize($chunkFile);
            $totalSize += $chunkSize;
            
            $in = fopen($chunkFile, 'rb');
            stream_copy_to_stream($in, $out);
            fclose($in);
            
            if (($i + 1) % 10 === 0 || $i === $totalChunks - 1) {
                Log::info('VideoAssemble: progress', [
                    'upload_id' => $uploadId,
                    'chunks_assembled' => $i + 1,
                    'total_chunks' => $totalChunks,
                ]);
            }
        }

        fclose($out);

        Log::info('VideoAssemble: all chunks assembled', [
            'upload_id' => $uploadId,
            'total_chunks' => $totalChunks,
            'total_size_bytes' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'output_path' => $outPath,
        ]);

        // Clean up temp chunks
        $deletedChunks = 0;
        foreach (glob($chunkDir . '/*') as $f) {
            @unlink($f);
            $deletedChunks++;
        }
        @rmdir($chunkDir);
        
        Log::info('VideoAssemble: cleanup completed', [
            'upload_id' => $uploadId,
            'deleted_chunks' => $deletedChunks,
        ]);

        $url = Storage::disk('public')->url($outName);
        event(new VideoUploaded($url));
        
        Log::info('VideoAssemble: success', [
            'upload_id' => $uploadId,
            'path' => $outName,
            'url' => $url,
            'final_size_mb' => round($totalSize / 1024 / 1024, 2),
        ]);

        return response()->json(['ok' => true, 'path' => $outName, 'url' => $url]);
    }
}
