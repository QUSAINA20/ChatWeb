<x-app-layout>
    <x-slot name="header">
        <div class="bg-purple-500 p-4 flex justify-between items-center fixed top-0 left-0 right-0 z-10">
            <h2 class="text-white text-xl font-semibold p-2">{{ $otherUser->name }}</h2>
            <span id="other-user-status" class="flex-shrink-0 w-4 h-4 rounded-full mr-auto p-2"></span>
        </div>
    </x-slot>

    <div class="flex flex-col h-screen pt-16">
        <div class="flex flex-grow overflow-hidden">
            <!-- Chat List -->
            <div class="w-1/4 bg-white dark:bg-gray-800 p-4 overflow-y-auto chat-list border-r border-gray-200">
                <h3 class="text-lg font-semibold mb-2 text-white">Your Chats</h3>
                <ul class="space-y-2">
                    @foreach ($userChats as $userChat)
                        <li>
                            <a href="{{ route('chat.show', ['chat' => $userChat->id]) }}"
                                class="block p-2 rounded-lg transition duration-300 ease-in-out hover:bg-gray-100 dark:hover:bg-gray-700
                                       @if ($userChat->id == $chat->id) bg-purple-200 dark:bg-purple-700 @endif text-white"
                                data-chat-id="{{ $userChat->id }}">
                                {{ $userChat->user1->id == auth()->id() ? $userChat->user2->name : $userChat->user1->name }}
                                <span class="ml-2 text-xs flex-shrink-0 w-4 h-4 rounded-full"
                                    id="user-status-{{ $userChat->user1->id == auth()->id() ? $userChat->user2->id : $userChat->user1->id }}"></span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Chat Window -->
            <div class="flex-grow flex flex-col bg-purple-50 dark:bg-gray-900">
                <div class="flex-grow p-4 overflow-y-auto chat-messages-container" id="chat-messages">
                    <ul class="space-y-4">
                        @foreach ($messages as $message)
                            <li class="flex @if ($message->user_id !== auth()->id()) flex-row-reverse @endif text-white">
                                <div
                                    class="max-w-xs p-3 rounded-lg @if ($message->user_id === auth()->id()) bg-purple-500 text-white @else bg-white dark:bg-purple-400 @endif shadow-md">
                                    <p class="mt-1">
                                        {{ $message->message }}
                                    </p>

                                    <span
                                        class="block text-xs mt-1 text-gray-500 dark:text-white @if ($message->user_id !== auth()->id()) mr-auto @endif">
                                        {{ $message->created_at->format('H:i') }}
                                    </span>

                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Chat Input -->
                <div class="bg-purple-200 dark:bg-gray-700 p-4 shadow-md">
                    <form id="chat-form" class="flex items-center space-x-2">
                        @csrf
                        <input type="hidden" name="chat_id" id="chat_id" value="{{ $chat->id }}">
                        <input id="input-message" type="text" name="message" placeholder="Type your message..."
                            class="flex-grow px-4 py-2 rounded-full focus:outline-none focus:border-purple-500 dark:bg-gray-700 dark:border-gray-600">
                        <button type="submit"
                            class="bg-purple-600 text-white px-4 py-2 rounded-full hover:bg-purple-700 focus:outline-none">Send</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<style>
    /* Custom scrollbar for chat messages */
    .chat-messages-container::-webkit-scrollbar {
        width: 12px;
    }

    .chat-messages-container::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .chat-messages-container::-webkit-scrollbar-thumb {
        background-color: #888;
        border-radius: 10px;
        border: 3px solid #f1f1f1;
    }

    .chat-messages-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .chat-list::-webkit-scrollbar {
        width: 8px;
    }

    .chat-list::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .chat-list::-webkit-scrollbar-thumb {
        background-color: #888;
        border-radius: 10px;
    }

    .chat-list::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.3/dist/sweetalert2.all.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const otherUserStatusElement = document.getElementById("other-user-status");
        const chatMessagesContainer = document.getElementById("chat-messages");
        const chatForm = document.getElementById("chat-form");
        const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;
        const chatId = document.getElementById("chat_id").value;

        @auth
        const userId = {{ auth()->id() }};
        const otherUserId = {{ $otherUser->id }};

        const chatList = document.querySelector(".chat-list");
        const newMessageNotificationChannel = Echo.private(`notifications.${userId}`);

        newMessageNotificationChannel.subscribed(() => {
            console.log('subscribed to newMessageNotificationChannel');
        }).listen('.new-message', (e) => {
            console.log('New message notification received:', e);
            if (e.chatId != chatId) {
                showNewMessageNotification(e.senderName, e.chatMessage);
            }
            boldChatFromNotification(e.chatId);
        });

        const chatPresenceChannel = Echo.join(`public-chat.${chatId}`)
            .here(users => {
                console.log('Users currently in the chat:', users);
                const otherUserOnline = users.some(user => user.id === otherUserId);
                updateOtherUserStatus(otherUserOnline);
            })
            .joining(user => {
                console.log('User joining:', user);
                if (user.id === otherUserId) {
                    updateOtherUserStatus(true);
                }
            })
            .leaving(user => {
                console.log('User leaving:', user);
                if (user.id === otherUserId) {
                    updateOtherUserStatus(false);
                }
            });
    @endauth

    function updateOtherUserStatus(isOnline) {
        otherUserStatusElement.classList.toggle('bg-white ', isOnline);
        otherUserStatusElement.classList.toggle('bg-purple-400', !isOnline);
    }

    chatPresenceChannel.listen(".new-chat-message", (e) => {
        const newMessage = e.message;
        console.log(newMessage);
        appendMessageToChatUI(newMessage);

        const lastMessage = chatMessagesContainer.lastElementChild;
        lastMessage.scrollIntoView({
            behavior: 'smooth'
        });
    });

    chatForm.addEventListener("submit", async (event) => {
        event.preventDefault();

        const userInput = document.getElementById("input-message").value;

        try {
            const response = await axios.post("/send-message", {
                _token: csrfToken,
                chat_id: chatId,
                type: 'individual',
                message: userInput,
            });

            document.getElementById("input-message").value = "";

            const lastMessage = chatMessagesContainer.lastElementChild;
            lastMessage.scrollIntoView({
                behavior: 'smooth'
            });
        } catch (error) {
            console.error("Error sending message:", error);
        }
    });

    function appendMessageToChatUI(message) {
        const messageElement = document.createElement("li");
        messageElement.classList.add("flex", "space-x-2");
        if (message.user_id !== userId) {
            messageElement.classList.add("flex-row-reverse");
        }

        const messageBubble = document.createElement("div");
        messageBubble.classList.add("max-w-xs", "p-4", "rounded-lg", "shadow-md");
        messageBubble.classList.add(message.user_id === userId ? "bg-purple-500 text-white" :
            "bg-white dark:bg-gray-700");

        const senderName = document.createElement("span");
        senderName.classList.add("block", "font-semibold");
        senderName.innerText = `${message.user.name}:`;

        const messageText = document.createElement("p");
        messageText.classList.add("mt-1");
        messageText.innerText = message.message;

        const messageTime = document.createElement("span");
        messageTime.classList.add("block", "text-xs", "mt-1", "text-gray-500", "dark:text-gray-400");
        messageTime.innerText = message.created_at;

        messageBubble.appendChild(senderName);
        messageBubble.appendChild(messageText);
        messageBubble.appendChild(messageTime);
        messageElement.appendChild(messageBubble);
        chatMessagesContainer.appendChild(messageElement);

        const lastMessage = chatMessagesContainer.lastElementChild;
        lastMessage.scrollIntoView({
            behavior: 'smooth'
        });
    }

    function boldChatFromNotification(chatId) {
        const chatLink = chatList.querySelector(`a[data-chat-id="${chatId}"]`);

        if (chatLink) {
            chatLink.classList.add("font-semibold");
        }
    }

    function showNewMessageNotification(senderName, message) {
        Swal.fire({
            title: `New Message from ${senderName}`,
            text: `${message}`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Open Chat',
            cancelButtonText: 'Dismiss'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('chat.show', ['chat' => $userChat->id]) }}"
            }
        });
    }
    });
</script>
