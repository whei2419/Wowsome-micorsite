<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanternController extends Controller
{
    /**
     * Show the live view page for a lantern/station.
     */
    public function show($id)
    {
        return view('lantern.live', ['id' => $id]);
    }

    /**
     * Server-Sent Events endpoint that streams a simple live feed.
     * In production this would be backed by a real event source.
     */
    public function feed($id)
    {
        return response()->stream(function () use ($id) {
            $counter = 0;
            while (!connection_aborted()) {
                $payload = json_encode([
                    'id' => $id,
                    'timestamp' => now()->toDateTimeString(),
                    'value' => rand(0, 100),
                ]);

                echo "data: {$payload}\n\n";
                if (function_exists('ob_flush')) {
                    @ob_flush();
                }
                @flush();

                $counter++;
                if ($counter >= 30) {
                    break; // stop after some messages to avoid infinite streams in dev
                }

                sleep(1);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }
}
