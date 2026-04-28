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
            'has_chunk_data' => !empty($request->input('chunk_data')),
            'content_length' => $request->header('Content-Length'),
            'content_type' => $request->header('Content-Type'),
            'all_files' => array_keys($request->allFiles()),
            'all_inputs' => array_keys($request->all()),
            'raw_body_length' => strlen($request->getContent()),
        ]);

        // Try to get data from Laravel's normal input (works when JSON parses correctly)
        $uploadId   = $request->input('upload_id', '');
        $chunkIndex = (int) $request->input('chunk_index', -1);
        $filename   = basename((string) $request->input('filename', ''));
        $chunkData  = $request->input('chunk_data', ''); // base64-encoded chunk

        // If inputs are empty, try parsing raw JSON body (Apache control character workaround)
        if (empty($uploadId) && $request->getContent()) {
            Log::info('VideoChunk: Laravel input empty, trying raw JSON parse');
            $rawBody = $request->getContent();

            // Try standard JSON parse first
            $cleanBody = $rawBody;
            if (preg_match('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/', $cleanBody)) {
                Log::info('VideoChunk: detected control chars, cleaning');
                $cleanBody = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/', '', $cleanBody);
            }

            $decoded = json_decode($cleanBody, true, 512, JSON_INVALID_UTF8_IGNORE);
            $jsonError = json_last_error();

            // If still failing, try more aggressive cleaning
            if ($jsonError === JSON_ERROR_CTRL_CHAR) {
                Log::info('VideoChunk: still has control chars, trying aggressive strip');
                $cleanBody = preg_replace('/[\x00-\x1F\x7F]/', '', $rawBody);
                $decoded = json_decode($cleanBody, true, 512, JSON_INVALID_UTF8_IGNORE);
                $jsonError = json_last_error();
            }

            // If JSON parsing still fails, try manual field extraction (last resort for large bodies)
            if ($jsonError !== 0 && $jsonError === JSON_ERROR_CTRL_CHAR) {
                Log::info('VideoChunk: JSON parse failed, trying manual field extraction');

                // Log first 500 chars to see structure
                Log::info('VideoChunk: raw body preview', [
                    'first_500_chars' => substr($rawBody, 0, 500),
                    'last_100_chars' => substr($rawBody, -100),
                ]);

                // Extract fields using string position finding (more reliable than regex for large strings)
                // Find upload_id
                if (($pos = strpos($rawBody, '"upload_id"')) !== false) {
                    $start = strpos($rawBody, '"', $pos + 12); // After "upload_id":
                    if ($start !== false) {
                        $end = strpos($rawBody, '"', $start + 1);
                        if ($end !== false) {
                            $uploadId = substr($rawBody, $start + 1, $end - $start - 1);
                        }
                    }
                }

                // Find chunk_index
                if (($pos = strpos($rawBody, '"chunk_index"')) !== false) {
                    if (preg_match('/"chunk_index"\s*:\s*(\d+)/', substr($rawBody, $pos, 50), $matches)) {
                        $chunkIndex = (int) $matches[1];
                    }
                }

                // Find filename
                if (($pos = strpos($rawBody, '"filename"')) !== false) {
                    $start = strpos($rawBody, '"', $pos + 11); // After "filename":
                    if ($start !== false) {
                        $end = strpos($rawBody, '"', $start + 1);
                        if ($end !== false) {
                            $filename = basename(substr($rawBody, $start + 1, $end - $start - 1));
                        }
                    }
                }

                // Find chunk_data (large base64 string)
                if (($pos = strpos($rawBody, '"chunk_data"')) !== false) {
                    $start = strpos($rawBody, '"', $pos + 13); // After "chunk_data":
                    if ($start !== false) {
                        $end = strrpos($rawBody, '"'); // Last quote in the body (end of chunk_data value)
                        if ($end !== false && $end > $start) {
                            $chunkData = substr($rawBody, $start + 1, $end - $start - 1);
                        }
                    }
                }

                Log::info('VideoChunk: manually extracted fields', [
                    'upload_id' => $uploadId,
                    'chunk_index' => $chunkIndex,
                    'filename' => $filename,
                    'chunk_data_length' => strlen($chunkData),
                ]);

                $decoded = true; // Mark as successful extraction
            }

            Log::info('VideoChunk: raw JSON parse result', [
                'json_error' => $jsonError,
                'json_error_msg' => json_last_error_msg(),
                'decoded_keys' => is_array($decoded) ? array_keys($decoded) : 'manual_extraction',
            ]);

            if (is_array($decoded)) {
                $uploadId   = $decoded['upload_id'] ?? '';
                $chunkIndex = (int) ($decoded['chunk_index'] ?? -1);
                $filename   = basename((string) ($decoded['filename'] ?? ''));
                $chunkData  = $decoded['chunk_data'] ?? '';

                Log::info('VideoChunk: extracted from raw JSON', [
                    'upload_id' => $uploadId,
                    'chunk_index' => $chunkIndex,
                    'filename' => $filename,
                    'chunk_data_length' => strlen($chunkData),
                ]);
            }
        }

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
            Log::info('VideoChunk: decoding from base64', [
                'upload_id' => $uploadId,
                'chunk_index' => $chunkIndex,
                'base64_length' => strlen($chunkData),
            ]);

            $decoded = base64_decode($chunkData, true);
            if ($decoded === false) {
                Log::error('VideoChunk: base64 decode failed', [
                    'upload_id' => $uploadId,
                    'chunk_index' => $chunkIndex,
                ]);
                return response()->json(['error' => 'invalid_base64'], 422);
            }

            file_put_contents($chunkFile, $decoded);

            Log::info('VideoChunk: chunk stored successfully from base64', [
                'upload_id' => $uploadId,
                'chunk_index' => $chunkIndex,
                'decoded_bytes' => strlen($decoded),
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

        // If inputs are empty, try parsing raw JSON body (Apache control character workaround)
        if (empty($uploadId) && $request->getContent()) {
            Log::info('VideoAssemble: Laravel input empty, trying raw JSON parse');
            $rawBody = $request->getContent();

            // Clean control characters
            $cleanBody = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/', '', $rawBody);
            $decoded = json_decode($cleanBody, true, 512, JSON_INVALID_UTF8_IGNORE);
            $jsonError = json_last_error();

            if ($jsonError === JSON_ERROR_CTRL_CHAR) {
                $cleanBody = preg_replace('/[\x00-\x1F\x7F]/', '', $rawBody);
                $decoded = json_decode($cleanBody, true, 512, JSON_INVALID_UTF8_IGNORE);
                $jsonError = json_last_error();
            }

            // If JSON parsing still fails, try manual extraction
            if ($jsonError !== 0) {
                Log::info('VideoAssemble: JSON parse failed, trying manual extraction');
                if (preg_match('/"upload_id"\s*:\s*"([^"]+)"/', $rawBody, $matches)) {
                    $uploadId = $matches[1];
                }
                if (preg_match('/"total_chunks"\s*:\s*(\d+)/', $rawBody, $matches)) {
                    $totalChunks = (int) $matches[1];
                }
                if (preg_match('/"filename"\s*:\s*"([^"]+)"/', $rawBody, $matches)) {
                    $filename = basename($matches[1]);
                }
                $decoded = true; // Mark as extracted
            }

            if (is_array($decoded)) {
                $uploadId    = $decoded['upload_id'] ?? '';
                $totalChunks = (int) ($decoded['total_chunks'] ?? 0);
                $filename    = basename((string) ($decoded['filename'] ?? ''));

                Log::info('VideoAssemble: extracted from raw JSON', [
                    'upload_id' => $uploadId,
                    'total_chunks' => $totalChunks,
                    'filename' => $filename,
                ]);
            } elseif ($decoded === true) {
                Log::info('VideoAssemble: manually extracted fields', [
                    'upload_id' => $uploadId,
                    'total_chunks' => $totalChunks,
                    'filename' => $filename,
                ]);
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
