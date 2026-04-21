<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use App\Events\ViewerJoin;
use App\Events\WebrtcOffer;
use App\Events\WebrtcAnswer;

class WebrtcController extends BaseController
{
    protected function checkSecret(Request $request)
    {
        $secret = env('WEBRTC_SHARED_SECRET');
        if ($secret && $request->header('X-WEBRTC-SECRET') !== $secret) {
            abort(403, 'Invalid secret');
        }
    }

    public function viewerJoin(Request $request)
    {
        $this->checkSecret($request);
        $viewerId = $request->input('viewer_id') ?? $request->header('X-Viewer-Id') ?? uniqid('viewer_');
        event(new ViewerJoin($viewerId));
        return response()->json(['ok' => true, 'viewer_id' => $viewerId]);
    }

    public function offer(Request $request)
    {
        $this->checkSecret($request);
        $from = $request->input('from');
        $to = $request->input('to') ?? null;
        $sdp = $request->input('sdp');
        if (! $from || ! $sdp) {
            return response()->json(['error' => 'missing from or sdp'], 422);
        }
        event(new WebrtcOffer($from, $to, $sdp));
        return response()->json(['ok' => true]);
    }

    public function answer(Request $request)
    {
        $this->checkSecret($request);
        $from = $request->input('from');
        $to = $request->input('to') ?? null;
        $sdp = $request->input('sdp');
        if (! $from || ! $sdp) {
            return response()->json(['error' => 'missing from or sdp'], 422);
        }
        event(new WebrtcAnswer($from, $to, $sdp));
        return response()->json(['ok' => true]);
    }
}
