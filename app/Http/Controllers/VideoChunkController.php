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
        // Multipart form-data with base64 chunk_data as text field (avoids JSON parsing limits)
        $uploadId   = $request->input('upload_id', '');
        $chunkIndex = (int) $request->input('chunk_index', -1);
        $filename   = basename((string) $request->input('filename', ''));
        $chunkData  = $request->input('chunk_data', ''); // base64-encoded chunk

        Log::info('VideoChunk: multipart request received', [
            'method' => $request->method(),
            'ip' => $request->ip(),
            'upload_id' => $uploadId ?: '(empty)',
            'chunk_index' => $chunkIndex,
            'filename' => $filename ?: '(empty)',
            'chunk_data_length' => strlen($chunkData),
            'content_type' => $request->header('Content-Type'),
        ]);

        // Strict upload_id to prevent path traversal
        if (!preg_match('/^[a-zA-Z0-9_\-]{1,200}$/', $uploadId)) {
            Log::warning('VideoChunk: invalid upload_id', ['upload_id' => $uploadId]);
            return response()->json(['error' => 'invalid_upload_id'], 422);
        }

        // Basic validation
        if ($chunkIndex < 0 || !$filename) {
            Log::warning('VideoChunk: missing basic fields', [
                'chunk_index' => $chunkIndex,
                'filename' => $filename,
            ]);
            return response()->json(['error' => 'missing_fields'], 422);
        }

        $chunkDir = storage_path('app/chunks/' . $uploadId);
        if (!is_dir($chunkDir)) {
            mkdir($chunkDir, 0755, true);
        }
        $chunkFile = $chunkDir . '/' . $chunkIndex;

        // Support both multipart file upload (local dev) and base64 (production workaround for reverse proxy issues)
        if ($request->hasFile('file')) {
            Log::info('VideoChunk: storing from multipart file');
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
        } elseif ($chunkData) {
            // Clean base64: remove whitespace, newlines, carriage returns
            $chunkData = trim(preg_replace('/\s+/', '', $chunkData));

            Log::info('VideoChunk: decoding from base64', [
                'upload_id' => $uploadId,
                'chunk_index' => $chunkIndex,
                'base64_length' => strlen($chunkData),
                'base64_first_20' => substr($chunkData, 0, 20),
                'base64_last_20' => substr($chunkData, -20),
            ]);

            $decoded = base64_decode($chunkData, true);
            if ($decoded === false) {
                Log::error('VideoChunk: base64 decode failed', [
                    'upload_id' => $uploadId,
                    'chunk_index' => $chunkIndex,
                    'base64_sample' => substr($chunkData, 0, 100),
                ]);
                return response()->json(['error' => 'invalid_base64'], 422);
            }

            file_put_contents($chunkFile, $decoded);

            Log::info('VideoChunk: chunk stored successfully from base64', [
                'upload_id' => $uploadId,
                'chunk_index' => $chunkIndex,
                'decoded_bytes' => strlen($decoded),
                'written_to' => $chunkFile,
                'file_exists_after_write' => file_exists($chunkFile),
                'file_size_after_write' => filesize($chunkFile),
            ]);
        } else {
            Log::warning('VideoChunk: no file or chunk_data provided', [
                'has_file' => $request->hasFile('file'),
                'has_chunk_data' => !empty($chunkData),
            ]);
            return response()->json(['error' => 'missing_fields'], 422);
        }

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
            'raw_body_length' => strlen($request->getContent()),
        ]);

        $uploadId    = $request->input('upload_id', '');
        $totalChunks = (int) $request->input('total_chunks', 0);
        $filename    = basename((string) $request->input('filename', ''));

        // If inputs are empty, try json() method (alternative JSON accessor)
        if (empty($uploadId) && $request->json('upload_id')) {
            Log::info('VideoAssemble: Laravel input empty, trying json() accessor');
            $uploadId    = $request->json('upload_id', '');
            $totalChunks = (int) $request->json('total_chunks', 0);
            $filename    = basename((string) $request->json('filename', ''));
        }

        // If still empty, try parsing raw JSON body (Apache control character workaround)
        if (empty($uploadId) && $request->getContent()) {
            Log::info('VideoAssemble: Laravel input empty, trying raw JSON parse');
            $rawBody = $request->getContent();

            // Try regex extraction FIRST (bypasses json_decode limits)
            if (preg_match('/"upload_id"\s*:\s*"([^"]+)"/', $rawBody, $m1) &&
                preg_match('/"total_chunks"\s*:\s*(\d+)/', $rawBody, $m2) &&
                preg_match('/"filename"\s*:\s*"([^"]+)"/', $rawBody, $m3)) {

                $uploadId    = $m1[1];
                $totalChunks = (int) $m2[1];
                $filename    = basename($m3[1]);

                Log::info('VideoAssemble: extracted via regex', [
                    'upload_id' => $uploadId,
                    'total_chunks' => $totalChunks,
                    'filename' => $filename,
                ]);
            }

            // Fallback: Try json_decode
            if (empty($uploadId)) {
                $cleanBody = preg_replace('/[\x00-\x1F\x7F]/', '', $rawBody);
                $decoded = json_decode($cleanBody, true, 512, JSON_INVALID_UTF8_IGNORE);
                $jsonError = json_last_error();

                Log::info('VideoAssemble: raw JSON parse result', [
                    'json_error' => $jsonError,
                    'json_error_msg' => json_last_error_msg(),
                    'decoded_keys' => is_array($decoded) ? array_keys($decoded) : null,
                    'body_length' => strlen($rawBody),
                ]);

                if (is_array($decoded)) {
                    $uploadId    = $decoded['upload_id'] ?? '';
                    $totalChunks = (int) ($decoded['total_chunks'] ?? 0);
                    $filename    = basename((string) ($decoded['filename'] ?? ''));

                    Log::info('VideoAssemble: extracted from raw JSON', [
                        'upload_id' => $uploadId,
                        'total_chunks' => $totalChunks,
                        'filename' => $filename,
                    ]);
                }
            }
        }

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
        $chunkSizes = [];
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
            $chunkSizes[$i] = $chunkSize;

            $in = fopen($chunkFile, 'rb');
            $copied = stream_copy_to_stream($in, $out);
            fclose($in);

            if ($copied !== $chunkSize) {
                Log::warning('VideoAssemble: size mismatch on copy', [
                    'chunk_index' => $i,
                    'expected' => $chunkSize,
                    'copied' => $copied,
                ]);
            }

            if (($i + 1) % 10 === 0 || $i === $totalChunks - 1) {
                Log::info('VideoAssemble: progress', [
                    'upload_id' => $uploadId,
                    'chunks_assembled' => $i + 1,
                    'total_chunks' => $totalChunks,
                    'bytes_so_far' => $totalSize,
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
            'output_file_size' => filesize($outPath),
            'chunk_sizes' => array_slice($chunkSizes, 0, 5, true) + ['...' => '...'] + array_slice($chunkSizes, -5, 5, true),
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
