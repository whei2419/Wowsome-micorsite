<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Upload;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string',
            'flowerId' => 'required|integer',
            'flowerName' => 'required|string',
            'name' => 'required|string',
            'message' => 'required|string',
            'sentAt' => 'required|date',
        ]);

        $upload = Upload::create([
            'client_id' => $validated['id'],
            'flower_id' => $validated['flowerId'],
            'flower_name' => $validated['flowerName'],
            'sender_name' => $validated['name'],
            'message' => $validated['message'],
            'sent_at' => Carbon::parse($validated['sentAt'])->setTimezone('UTC'),
        ]);

        broadcast(new MessageSent($upload));

        return response()->json([
            'success' => true,
            'message' => 'Sent successfully',
        ], 201);
    }

    public function latest()
    {
        $uploads = Upload::orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($upload) {
                return [
                    'id' => $upload->id,
                    'client_id' => $upload->client_id,
                    'flower_id' => $upload->flower_id,
                    'flower_name' => $upload->flower_name,
                    'sender_name' => $upload->sender_name,
                    'message' => $upload->message,
                    'sent_at' => $upload->sent_at,
                    'created_at' => $upload->created_at,
                ];
            });

        return response()->json($uploads);
    }
}
