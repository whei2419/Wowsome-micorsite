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
        // ── Full header dump to diagnose chunked vs content-length ────
        $allHeaders = $request->headers->all();
        $transferEncoding = $request->header('Transfer-Encoding');
        $contentLength    = $request->header('Content-Length');
        $serverContentLength = $_SERVER['CONTENT_LENGTH'] ?? 'not set';
        $serverContentType   = $_SERVER['CONTENT_TYPE']   ?? 'not set';

        \Log::info('CaptureUpload: request headers', [
            'transfer_encoding'        => $transferEncoding ?? '(not present)',
            'content_length_header'    => $contentLength    ?? '(not present)',
            'server_CONTENT_LENGTH'    => $serverContentLength,
            'server_CONTENT_TYPE'      => $serverContentType,
            'content_type'             => $request->header('Content-Type'),
            'http_version'             => $_SERVER['SERVER_PROTOCOL'] ?? 'unknown',
            'all_headers'              => $allHeaders,
        ]);

        \Log::info('CaptureUpload: request received', [
            'method'         => $request->method(),
            'ip'             => $request->ip(),
            'has_file_file'  => $request->hasFile('file'),
            'has_file_image' => $request->hasFile('image'),
            'has_input_image'=> $request->filled('image'),
            'image_input_length' => strlen((string) $request->input('image', '')),
            'all_files'      => array_keys($request->allFiles()),
            'all_inputs'     => array_keys($request->all()),
            'input_keys'     => array_keys($request->input()),
            'json_keys'      => $request->json() ? array_keys($request->json()->all()) : null,
            'files_superglobal_count' => count($_FILES),
            'raw_body_length' => strlen($request->getContent()),
            'raw_body_preview' => substr($request->getContent(), 0, 200),
            'raw_FILES'      => array_map(fn($f) => [
                'name'     => $f['name']     ?? null,
                'type'     => $f['type']     ?? null,
                'size'     => $f['size']     ?? null,
                'tmp_name' => $f['tmp_name'] ?? null,
                'error'    => $f['error']    ?? null,
            ], $_FILES),
        ]);

        // Try to get image from various sources
        $imageData = null;
        $source = null;
        $path = null;

        // Check multipart file upload
        if ($request->hasFile('file') || $request->hasFile('image')) {
            $uploadedFile = $request->file('file') ?? $request->file('image');
            $source = 'multipart_file';

            \Log::info('CaptureUpload: multipart file received', [
                'original_name' => $uploadedFile->getClientOriginalName(),
                'mime_type'     => $uploadedFile->getMimeType(),
                'client_mime'   => $uploadedFile->getClientMimeType(),
                'size_bytes'    => $uploadedFile->getSize(),
                'is_valid'      => $uploadedFile->isValid(),
                'error_code'    => $uploadedFile->getError(),
            ]);

            $path = $uploadedFile->store('captures', 'public');
            if (! $path) {
                \Log::error('CaptureUpload: file store failed', [
                    'original_name' => $uploadedFile->getClientOriginalName(),
                ]);
                return response()->json(['error' => 'file_store_failed'], 500);
            }
            \Log::info('CaptureUpload: file stored from multipart', ['path' => $path]);
        } else {
            // Try getting base64 data from various sources
            \Log::info('CaptureUpload: no multipart file, trying other sources');

            if ($request->filled('image')) {
                $source = 'input_image';
                $imageData = $request->input('image');
                \Log::info('CaptureUpload: found via input()', ['length' => strlen($imageData)]);
            } elseif ($request->json('image')) {
                $source = 'json_image';
                $imageData = $request->json('image');
                \Log::info('CaptureUpload: found via json()', ['length' => strlen($imageData)]);
            } else {
                // Try parsing raw body as JSON
                $rawBody = $request->getContent();
                \Log::info('CaptureUpload: trying raw JSON parsing', [
                    'raw_length' => strlen($rawBody),
                    'starts_with_brace' => !empty($rawBody) && str_starts_with(trim($rawBody), '{'),
                    'first_50_chars' => substr($rawBody, 0, 50),
                    'has_null_bytes' => strpos($rawBody, "\0") !== false,
                    'has_control_chars' => preg_match('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/', $rawBody) === 1,
                ]);

                if (!empty($rawBody) && str_starts_with(trim($rawBody), '{')) {
                    $source = 'raw_json';
                    try {
                        // Pre-clean the body to remove control characters
                        // Apache/proxies sometimes add these during transmission
                        $cleanBody = $rawBody;

                        // Remove all control characters except tab, newline, carriage return
                        if (preg_match('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/', $cleanBody)) {
                            \Log::info('CaptureUpload: detected control chars, cleaning');
                            $cleanBody = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/', '', $cleanBody);
                        }

                        // Also try removing ALL whitespace from the base64 data if still failing
                        // This handles cases where newlines are in the base64 string

                        $decoded = json_decode($cleanBody, true, 512, JSON_INVALID_UTF8_IGNORE);
                        $jsonError = json_last_error();

                        // If still failing with control chars, try more aggressive cleaning
                        if ($jsonError === JSON_ERROR_CTRL_CHAR) {
                            \Log::info('CaptureUpload: still has control chars after cleaning, trying aggressive strip');
                            // Remove ALL control chars including \r\n\t
                            $cleanBody = preg_replace('/[\x00-\x1F\x7F]/', '', $rawBody);
                            $decoded = json_decode($cleanBody, true, 512, JSON_INVALID_UTF8_IGNORE);
                            $jsonError = json_last_error();
                        }

                        \Log::info('CaptureUpload: json_decode result', [
                            'decoded_is_null' => is_null($decoded),
                            'decoded_is_array' => is_array($decoded),
                            'json_error' => $jsonError,
                            'json_error_msg' => json_last_error_msg(),
                            'has_image_key' => isset($decoded['image']),
                            'decoded_keys' => is_array($decoded) ? array_keys($decoded) : null,
                            'body_length_before_clean' => strlen($rawBody),
                            'body_length_after_clean' => strlen($cleanBody),
                        ]);

                        if (isset($decoded['image'])) {
                            $imageData = $decoded['image'];
                            \Log::info('CaptureUpload: extracted from raw JSON', ['length' => strlen($imageData)]);
                        } else {
                            \Log::warning('CaptureUpload: raw JSON parsed but no image key');
                        }
                    } catch (\Exception $e) {
                        \Log::error('CaptureUpload: JSON parse error', ['error' => $e->getMessage()]);
                    }
                } else {
                    \Log::warning('CaptureUpload: raw body empty or does not start with {');
                }
            }

            // Process base64 image data if we have it
            if ($imageData) {
                \Log::info("CaptureUpload: processing base64 from {$source}", [
                    'input_length' => strlen($imageData),
                    'has_data_uri' => str_starts_with($imageData, 'data:'),
                    'source' => $source,
                ]);

                $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
                $decoded = base64_decode($base64, strict: true);
                if ($decoded === false) {
                    \Log::error('CaptureUpload: base64 decode failed', [
                        'input_length' => strlen($imageData),
                        'source' => $source,
                    ]);
                    return response()->json(['error' => 'invalid_base64'], 422);
                }
                $path = 'captures/'.uniqid('cap_').'.png';
                Storage::disk('public')->put($path, $decoded);
                \Log::info('CaptureUpload: base64 image stored', [
                    'path'         => $path,
                    'decoded_bytes'=> strlen($decoded),
                    'source' => $source,
                ]);
            }
        }

        // If we still don't have a path, nothing worked
        if (!$path) {
            \Log::warning('CaptureUpload: no image provided in request', [
                'content_type'  => $request->header('Content-Type'),
                'content_length'=> $request->header('Content-Length'),
                'all_files'     => array_keys($request->allFiles()),
                'all_inputs'    => array_keys($request->all()),
                'raw_body_size' => strlen($request->getContent()),
            ]);
            return response()->json(['error' => 'no_image_provided'], 422);
        }

        $url = Storage::disk('public')->url($path);
        event(new CaptureUploaded($url));

        \Log::info('CaptureUpload: success', ['path' => $path, 'url' => $url]);

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
