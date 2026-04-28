<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use App\Events\VideoUploaded;

class ChunkedVideoUploadController extends BaseController
{
    /**
     * Handle chunked video upload using laravel-chunk-upload package.
     * Supports resumable uploads with standard multipart/form-data.
     *
     * POST /api/upload-video/chunked
     */
    public function upload(Request $request)
    {
        Log::info('ChunkedVideoUpload: request received', [
            'method' => $request->method(),
            'ip' => $request->ip(),
            'has_file' => $request->hasFile('file'),
            'content_length' => $request->header('Content-Length'),
            'content_type' => $request->header('Content-Type'),
            'resumable_identifier' => $request->input('resumableIdentifier', '(empty)'),
            'resumable_filename' => $request->input('resumableFilename', '(empty)'),
            'resumable_chunk_number' => $request->input('resumableChunkNumber', '(empty)'),
            'resumable_total_chunks' => $request->input('resumableTotalChunks', '(empty)'),
        ]);

        // Create the file receiver
        try {
            $receiver = new FileReceiver("file", $request, HandlerFactory::classFromRequest($request));
        } catch (UploadMissingFileException $e) {
            Log::error('ChunkedVideoUpload: missing file', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'missing_file',
                'message' => $e->getMessage()
            ], 422);
        }

        // Check if the upload is complete
        if ($receiver->isUploaded() === false) {
            Log::warning('ChunkedVideoUpload: upload not complete');
            throw new UploadMissingFileException();
        }

        // Receive the chunk
        $save = $receiver->receive();

        // Check if the upload has finished (all chunks received)
        if ($save->isFinished()) {
            Log::info('ChunkedVideoUpload: all chunks received, finalizing');
            return $this->saveFile($save->getFile());
        }

        // Get upload progress
        $handler = $save->handler();
        $percentageDone = $handler->getPercentageDone();

        Log::info('ChunkedVideoUpload: chunk received', [
            'percentage_done' => $percentageDone,
            'chunk_number' => $request->input('resumableChunkNumber'),
        ]);

        return response()->json([
            'done' => $percentageDone,
            'status' => true,
            'message' => 'Chunk uploaded successfully'
        ]);
    }

    /**
     * Save the finalized file to storage and fire VideoUploaded event.
     */
    protected function saveFile($file)
    {
        $filename = basename($file->getClientOriginalName());

        Log::info('ChunkedVideoUpload: saving final file', [
            'filename' => $filename,
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ]);

        // Move to public storage
        $path = $file->store('videos', 'public');

        if (!$path) {
            Log::error('ChunkedVideoUpload: failed to store file');
            return response()->json(['error' => 'storage_failed'], 500);
        }

        $url = Storage::disk('public')->url($path);

        Log::info('ChunkedVideoUpload: file saved successfully', [
            'path' => $path,
            'url' => $url,
        ]);

        // Fire VideoUploaded event
        event(new VideoUploaded($path, $url));

        // Delete the temporary chunk folder
        $chunkPath = $file->getPath();
        if (is_dir($chunkPath)) {
            @rmdir($chunkPath);
        }

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => $url,
            'filename' => $filename,
        ]);
    }
}
