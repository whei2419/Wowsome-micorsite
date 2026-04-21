<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class CaptureUploaded implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function broadcastOn()
    {
        return new Channel('camera-control');
    }

    public function broadcastAs()
    {
        return 'capture:uploaded';
    }

    public function broadcastWith()
    {
        return ['url' => $this->url];
    }
}
