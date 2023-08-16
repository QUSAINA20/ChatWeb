<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $chatId;
    public $senderId;
    public $reciverId;
    public $senderName;
    public $chatMessage;


    public function __construct($chatId, $senderId,  $reciverId, $senderName, $chatMessage)
    {
        $this->chatId = $chatId;
        $this->chatMessage = $chatMessage;
        $this->senderId = $senderId;
        $this->reciverId = $reciverId;
        $this->senderName = $senderName;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('notifications.' . $this->reciverId);
    }
    public function broadcastAs()
    {
        return 'new-message';
    }
    public function broadcastWith()
    {
        return [
            'chatId' => $this->chatId,
            'senderId' => $this->senderId,
            'senderName' => $this->senderName,
            'chatMessage' => $this->chatMessage,
        ];
    }
}
