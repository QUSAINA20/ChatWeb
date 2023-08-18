<?php

namespace App\Http\Controllers;

use App\Events\JoinGroupChat;
use App\Events\NewChatMessage;
use App\Events\NewGroupMessage;
use App\Events\NewMessageNotification;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{

    public function create()
    {
        $users = User::where('id', '!=', auth()->id())->get();

        return view('chats.create', compact('users'));
    }
    public function createChat(Request $request)
    {
        // Create a one-on-one chat
        $chat = new Chat();
        $chat->user1_id = auth()->id();
        $chat->user2_id = $request->user_id;
        $chat->save();
        return redirect()->route('chat.show', $chat);
    }


    public function sendMessage(Request $request)
    {
        if ($request->chat_id) {
            $chat = Chat::findOrFail($request->chat_id);
        };
        if ($request->group_id) {
            $group = Group::findOrFail($request->group_id);
        };

        $otherUserId = null;

        if ($request->type == 'individual') {
            $otherUserId = ($chat->user1_id === auth()->id()) ? $chat->user2_id : $chat->user1_id;
        }

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);


        event(new NewChatMessage($message));
        event(new NewMessageNotification($chat->id, auth()->id(), $otherUserId, auth()->user()->name, $request->message));


        return response()->json(['message' => 'Message sent successfully']);
    }


    public function show(Chat $chat)
    {
        $messages = $chat->messages()->with('user')->get();
        $userId = auth()->id();

        // Get the user's chats
        $userChats = Chat::where('user1_id', $userId)
            ->orWhere('user2_id', $userId)
            ->get();
        $otherUser = $userId == $chat->user1_id ? $chat->user2 : $chat->user1;


        return view('chats.show', compact('userId', 'chat', 'messages', 'userChats', 'otherUser'));
    }
}
