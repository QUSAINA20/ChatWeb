<?php

namespace App\Listeners;

use App\Events\JoinGroupChat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Broadcast;

class SubscribeToGroupChat
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(JoinGroupChat $event)
    {
        $user = auth()->user();
        $groupChannel = 'group-chat.' . $event->group->id;

        if ($user && $event->group) {
            Broadcast::socket($user->id)->channel($groupChannel)->join();
        }
    }
}
