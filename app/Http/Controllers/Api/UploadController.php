<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Upload;
use Illuminate\Http\Request;
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
            'data_text' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        // ✅ Store image
        $path = $request->file('image')
            ->store('hadalaboo/uploads/images', 'public');

        // ✅ Save DB record
        $upload = Upload::create([
            'image_path' => $path,
            'data_text'  => $validated['data_text'],
        ]);

        // ✅ JSON response
        return response()->json([
            'success' => true,
            'message' => 'Upload successful',
            'data' => [
                'id' => $upload->id,
                'image_url' => Storage::disk('public')->url($path),
                'data_text' => $upload->data_text,
                'created_at' => $upload->created_at,
            ],
        ], 201);
    }
}
