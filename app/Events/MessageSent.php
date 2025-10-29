<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;



class MessageSent  implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $latitude;
    public $longitude;


    public function __construct($userId, $latitude, $longitude)
    {

        $this->userId = $userId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }


    public function broadcastOn()
    {
        return new Channel('chat-room');
        }
   public function broadcastAs()
    {
        return 'MessageSent';
    }
    public function broadcastWith()
    {
        return [
            'id' => $this->userId,
            'lat' => $this->latitude,
            'lng' => $this->longitude,
            'timestamp' => now()->timestamp,
        ];
    }
}
