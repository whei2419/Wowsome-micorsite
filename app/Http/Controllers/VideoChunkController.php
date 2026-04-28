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

        // If inputs are empty, try json() method (alternative JSON accessor)
        if (empty($uploadId) && $request->json('upload_id')) {
            Log::info('VideoChunk: Laravel input empty, trying json() accessor');
            $uploadId   = $request->json('upload_id', '');
            $chunkIndex = (int) $request->json('chunk_index', -1);
            $filename   = basename((string) $request->json('filename', ''));
            $chunkData  = $request->json('chunk_data', '');
        }

        // If still empty, try parsing raw JSON body (Apache control character workaround)
        if (empty($uploadId) && $request->getContent()) {
            Log::info('VideoChunk: Laravel input empty, trying raw JSON parse');

            // Try multiple methods to read the raw body
            $rawBody = $request->getContent();
            $phpInput = file_get_contents('php://input');

            Log::info('VideoChunk: body source comparison', [
                'getContent_length' => strlen($rawBody),
                'php_input_length' => strlen($phpInput),
                'sources_match' => $rawBody === $phpInput,
                'getContent_first_100' => substr($rawBody, 0, 100),
                'php_input_first_100' => substr($phpInput, 0, 100),
            ]);

            // Use whichever is longer (in case one is truncated)
            if (strlen($phpInput) > strlen($rawBody)) {
                Log::info('VideoChunk: using php://input instead of getContent()');
                $rawBody = $phpInput;
            }

            // Detailed diagnostics: check for control characters and body structure
            $controlCharCount = preg_match_all('/[\x00-\x1F\x7F]/', $rawBody, $matches);
            $first200 = substr($rawBody, 0, 200);
            $last500 = substr($rawBody, -500);  // Check last 500 chars to see if metadata is there
            $contentLength = $request->header('Content-Length');
            $actualLength = strlen($rawBody);

            Log::info('VideoChunk: raw body diagnostics', [
                'content_length_header' => $contentLength,
                'actual_body_length' => $actualLength,
                'length_mismatch' => $contentLength != $actualLength,
                'control_char_count' => $controlCharCount,
                'first_200_chars' => $first200,
                'last_500_chars' => $last500,
                'ends_with_closing_brace' => substr(rtrim($rawBody), -1) === '}',
                'starts_with_brace' => substr(ltrim($rawBody), 0, 1) === '{',
                'has_upload_id_string' => strpos($rawBody, '"upload_id"') !== false ? 'YES' : 'NO',
                'has_chunk_index_string' => strpos($rawBody, '"chunk_index"') !== false ? 'YES' : 'NO',
            ]);

            // PURE string-position extraction (NO REGEX - regex fails on 1MB+ bodies due to PCRE limits)
            // The JSON structure: {"chunk_data":"<1.4MB base64>","chunk_index":0,"filename":"...","total_chunks":13,"upload_id":"..."}
            // Strategy: Use strrpos() to search from END backwards (avoids scanning through huge chunk_data)

            $uploadId = '';
            $chunkIndex = -1;
            $filename = '';
            $chunkData = '';

            // Find upload_id (search from end backwards)
            $uploadIdPos = strrpos($rawBody, '"upload_id"');
            if ($uploadIdPos !== false) {
                $colonPos = strpos($rawBody, ':', $uploadIdPos);
                if ($colonPos !== false) {
                    $openQuote = strpos($rawBody, '"', $colonPos);
                    if ($openQuote !== false) {
                        $closeQuote = strpos($rawBody, '"', $openQuote + 1);
                        if ($closeQuote !== false) {
                            $uploadId = substr($rawBody, $openQuote + 1, $closeQuote - $openQuote - 1);
                        }
                    }
                }
            }

            Log::info('VideoChunk: upload_id extraction debug', [
                'found_position' => $uploadIdPos !== false ? 'YES' : 'NO',
                'upload_id_pos' => $uploadIdPos,
                'extracted_value' => $uploadId ?: '(empty)',
                'body_tail' => $uploadIdPos !== false ? substr($rawBody, max(0, $uploadIdPos - 50), 150) : null,
            ]);

            // Find chunk_index (search from end backwards)
            $chunkIndexPos = strrpos($rawBody, '"chunk_index"');
            if ($chunkIndexPos !== false) {
                $colonPos = strpos($rawBody, ':', $chunkIndexPos);
                if ($colonPos !== false) {
                    // chunk_index is a number, not a string, so no quotes
                    $commaPos = strpos($rawBody, ',', $colonPos);
                    if ($commaPos !== false) {
                        $chunkIndexStr = trim(substr($rawBody, $colonPos + 1, $commaPos - $colonPos - 1));
                        $chunkIndex = (int) $chunkIndexStr;
                    }
                }
            }

            // Find filename (search from end backwards)
            $filenamePos = strrpos($rawBody, '"filename"');
            if ($filenamePos !== false) {
                $colonPos = strpos($rawBody, ':', $filenamePos);
                if ($colonPos !== false) {
                    $openQuote = strpos($rawBody, '"', $colonPos);
                    if ($openQuote !== false) {
                        $closeQuote = strpos($rawBody, '"', $openQuote + 1);
                        if ($closeQuote !== false) {
                            $filename = basename(substr($rawBody, $openQuote + 1, $closeQuote - $openQuote - 1));
                        }
                    }
                }
            }

            Log::info('VideoChunk: field extraction results', [
                'upload_id' => $uploadId ?: '(empty)',
                'chunk_index' => $chunkIndex,
                'filename' => $filename ?: '(empty)',
            ]);

            // Extract chunk_data using position-based approach (comes first, is huge)
            if (!empty($uploadId) && $chunkIndex >= 0) {
                $chunkDataPos = strpos($rawBody, '"chunk_data"');
                if ($chunkDataPos !== false) {
                    // Find the opening quote after "chunk_data":
                    $startQuote = strpos($rawBody, '"', $chunkDataPos + 12);
                    if ($startQuote !== false) {
                        $startQuote++; // Move past the quote
                        // Find where chunk_data value ends (next unescaped quote followed by comma)
                        $endQuote = strpos($rawBody, '","', $startQuote);
                        if ($endQuote !== false && $endQuote > $startQuote) {
                            $chunkData = substr($rawBody, $startQuote, $endQuote - $startQuote);

                            Log::info('VideoChunk: extracted all fields successfully', [
                                'upload_id' => $uploadId,
                                'chunk_index' => $chunkIndex,
                                'filename' => $filename,
                                'chunk_data_length' => strlen($chunkData),
                            ]);
                        }
                    }
                }
            }

            // Fallback: Try json_decode (will likely fail on large strings but worth trying)
            if (empty($uploadId)) {
                // Clean control characters that Apache may inject during transmission
                $cleanBody = preg_replace('/[\x00-\x1F\x7F]/', '', $rawBody);
                $decoded = json_decode($cleanBody, true, 512, JSON_INVALID_UTF8_IGNORE);
                $jsonError = json_last_error();

                Log::info('VideoChunk: raw JSON parse result', [
                    'json_error' => $jsonError,
                    'json_error_msg' => json_last_error_msg(),
                    'decoded_keys' => is_array($decoded) ? array_keys($decoded) : null,
                    'body_length' => strlen($rawBody),
                    'clean_body_length' => strlen($cleanBody),
                    'bytes_removed' => strlen($rawBody) - strlen($cleanBody),
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
