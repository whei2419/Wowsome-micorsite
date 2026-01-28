<?php

namespace App\Http\Controllers\Api;

use App\Models\Upload;
use Illuminate\Http\Request;
use App\Events\ImageUploaded;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        // ✅ Validate request
        $validated = $request->validate([
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120', // 5MB
            ],
        ]);

        // ✅ Store image
        $path = $request->file('image')
            ->store('hadalaboo/uploads/images', 'public');

        // ✅ Save DB record
        $upload = Upload::create([
            'image_path' => $path,
        ]);

        // ✅ Fire event
        broadcast(new ImageUploaded($upload));

        // ✅ JSON response
        return response()->json([
            'success' => true,
            'message' => 'Upload successful',
            'data' => [
                'image_url' => Storage::disk('public')->url($path),
                'view_url'     => route('lantern.view', $upload->id),
                'download_url' => route('lantern.download', $upload->id),
                'created_at' => $upload->created_at,
            ],
        ], 201);
    }
}
