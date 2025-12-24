<?php
namespace App\Events\Prod;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Prod; 

class ProdAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The product instance.
     */
    public $prod;
    
    /**
     * Create a new event instance.
     */
    public function __construct(Prod $prod)
    {
        $this->prod = $prod;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    /*
    public function broadcastOn() : array 
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
    */
}
