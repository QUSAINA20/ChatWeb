<?php

namespace App\Http\Controllers;


use App\Events\NewChatMessage;
use App\Events\NewMessageNotification;
use App\Models\Chat;
use App\Models\ChatMessage;
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
        // Assuming the authenticated user is user1
        $chat = new Chat();
        $chat->user1_id = auth()->id();
        $chat->user2_id = $request->user_id;
        $chat->save();
        return redirect()->route('chat.show', $chat);
    }

    public function sendMessage(Request $request)
    {
        $chat = Chat::findOrFail($request->chat_id);

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);
        $otherUserId = ($chat->user1_id === auth()->id()) ? $chat->user2_id : $chat->user1_id;

        event(new NewChatMessage($message));


        event(new NewMessageNotification($chat->id, auth()->id(), $otherUserId, auth()->user()->name, $request->message));

        Log::info('NewMessageNotification event dispatched', [
            'chatId' => $chat->id,
            'senderId' => auth()->id(),
            'receiverId' => $otherUserId,
            'senderName' => auth()->user()->name,
            'chatMessage' => $request->message
        ]);

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
