<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class TriggerCapture implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $by;

    public function __construct($by = null)
    {
        $this->by = $by;
    }

    public function broadcastOn()
    {
        return new Channel('camera-control');
    }

    public function broadcastAs()
    {
        return 'capture';
    }

    public function broadcastWith()
    {
        return [
            'by' => $this->by,
            'ts' => now()->toDateTimeString(),
        ];
    }
}
