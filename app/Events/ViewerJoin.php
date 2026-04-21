<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class ViewerJoin implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public string $viewerId;

    public function __construct(string $viewerId)
    {
        $this->viewerId = $viewerId;
    }

    public function broadcastOn()
    {
        return new Channel('camera-control');
    }

    public function broadcastAs()
    {
        return 'viewer-join';
    }
}
