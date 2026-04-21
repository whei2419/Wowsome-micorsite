<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class WebrtcAnswer implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public string $from;
    public string $to;
    public $sdp;

    public function __construct(string $from, string $to, $sdp)
    {
        $this->from = $from;
        $this->to = $to;
        $this->sdp = $sdp;
    }

    public function broadcastOn()
    {
        return new Channel('camera-control');
    }

    public function broadcastAs()
    {
        return 'webrtc-answer';
    }
}
