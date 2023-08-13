<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">{{ $chat->user1->name }} and {{ $chat->user2->name }}</h2>
            {{-- <a href="{{ route('home') }}" class="text-blue-500 hover:underline">Back to Chats</a> --}}
        </div>
    </x-slot>

    <div class="px-4 py-8 chat-window">
        <ul class="space-y-4">
            @foreach ($messages as $message)
                <li class="flex space-x-2">
                    <span class="font-semibold">{{ $message->user->name }}:</span>
                    <p>{{ $message->message }}</p>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 p-4 shadow-top">
        <form id="chat-form" action="{{ route('send-message') }}" method="post" class="flex space-x-2">
            @csrf
            <input type="hidden" name="chat_id"id="chat_id" value="{{ $chat->id }}">
            <input id="input-message" type="text" name="message" placeholder="Type your message..."
                class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600">
            <button type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none">Send</button>
        </form>
    </div>


</x-app-layout>
