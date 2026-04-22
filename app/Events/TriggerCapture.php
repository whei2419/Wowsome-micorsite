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
    public string $mode;
    public int $durationSec;

    public function __construct($by = null, string $mode = 'photo', int $durationSec = 10)
    {
        $this->by = $by;
        $this->mode = $mode;
        $this->durationSec = $durationSec;
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
            'mode' => $this->mode,
            'durationSec' => $this->durationSec,
            'ts' => now()->toDateTimeString(),
        ];
    }
}
