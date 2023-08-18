<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewGroupNotification  implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $receiverId;
    public $group;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($receiverId, $group)
    {
        $this->receiverId = $receiverId;
        $this->group = $group;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('notifications.' . $this->receiverId);
    }
    public function broadcastAs()
    {
        return 'new-group-notification';
    }
    public function broadcastWith()
    {
        return [
            'groupName' => $this->group->name,
            'groupId' => $this->group->id

        ];
    }
}
