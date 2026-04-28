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
            'all_inputs'     => array_keys($request->except(['image'])),
            'files_superglobal_count' => count($_FILES),
            'raw_body_length' => strlen($request->getContent()),
            'raw_FILES'      => array_map(fn($f) => [
                'name'     => $f['name']     ?? null,
                'type'     => $f['type']     ?? null,
                'size'     => $f['size']     ?? null,
                'tmp_name' => $f['tmp_name'] ?? null,
                'error'    => $f['error']    ?? null,
            ], $_FILES),
        ]);

        // accept multipart file under 'file' or 'image', or base64 string under 'image'
        if ($request->hasFile('file') || $request->hasFile('image')) {
            $uploadedFile = $request->file('file') ?? $request->file('image');
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
            \Log::info('CaptureUpload: file stored', ['path' => $path]);
        } elseif ($request->filled('image')) {
            $raw = $request->input('image');
            \Log::info('CaptureUpload: base64 image received', [
                'input_length' => strlen($raw),
                'has_data_uri' => str_starts_with($raw, 'data:'),
            ]);

            $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $raw);
            $decoded = base64_decode($base64, strict: true);
            if ($decoded === false) {
                \Log::error('CaptureUpload: base64 decode failed', [
                    'input_length' => strlen($raw),
                ]);

                return response()->json(['error' => 'invalid_base64'], 422);
            }
            $path = 'captures/'.uniqid('cap_').'.png';
            Storage::disk('public')->put($path, $decoded);
            \Log::info('CaptureUpload: base64 image stored', [
                'path'         => $path,
                'decoded_bytes'=> strlen($decoded),
            ]);
        } else {
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
