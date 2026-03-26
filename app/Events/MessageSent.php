<?php

namespace App\Events;

use App\Models\Upload;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    public function __construct(Upload $upload)
    {
        $this->data = [
            'id' => $upload->id,
            'client_id' => $upload->client_id,
            'flower_id' => $upload->flower_id,
            'flower_name' => $upload->flower_name,
            'sender_name' => $upload->sender_name,
            'message' => $upload->message,
            'sent_at' => $upload->sent_at,
        ];
    }

    public function broadcastOn()
    {
        return new Channel('messages');
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }
}
