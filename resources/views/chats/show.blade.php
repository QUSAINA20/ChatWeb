<x-app-layout>
    <x-slot name="header">
        <div class="bg-green-500 p-4 flex justify-between items-center fixed top-0 left-0 right-0 z-10">
            <h2 class="text-white text-xl font-semibold">{{ $otherUser->name }}</h2>
            <span class="text-xs ml-2" id="other-user-status"></span>
        </div>
    </x-slot>

    <div class="flex">
        <!-- Chat List -->
        <div class="w-1/4 bg-white dark:bg-gray-800 p-4 overflow-y-auto chat-list">
            <h3 class="text-lg font-semibold mb-2">Your Chats</h3>
            <ul class="space-y-2">
                @foreach ($userChats as $userChat)
                    <li>
                        <a href="{{ route('chat.show', ['chat' => $userChat->id]) }}"
                            class="block p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                            data-chat-id="{{ $userChat->id }}"> <!-- Add data-chat-id attribute -->
                            {{ $userChat->user1->name }} and {{ $userChat->user2->name }}
                            <span class="ml-2 text-xs"
                                id="user-status-{{ $userChat->user1->id == auth()->id() ? $userChat->user2->id : $userChat->user1->id }}"></span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Chat Window -->
        <div class="flex-grow px-4 py-8 chat-window bg-gray-100 overflow-y-auto" style="padding-bottom: 80px;">
            <ul class="space-y-4" id="chat-messages">
                @foreach ($messages as $message)
                    <li class="flex space-x-2 @if ($message->user_id !== auth()->id()) flex-row-reverse @endif">
                        <div
                            class="rounded-lg p-2 @if ($message->user_id === auth()->id()) bg-green-100 @else bg-white @endif shadow-md">
                            <span
                                class="font-semibold @if ($message->user_id === auth()->id()) text-green-800 @else text-gray-800 @endif">{{ $message->user->name }}:</span>
                            <p class="@if ($message->user_id === auth()->id()) text-green-700 @else text-gray-700 @endif">
                                {{ $message->message }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Chat Input -->
    <div class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 p-4 shadow-top" style="height: 80px;">
        <form id="chat-form" class="flex space-x-2 h-full">
            @csrf
            <input type="hidden" name="chat_id" id="chat_id" value="{{ $chat->id }}">
            <input id="input-message" type="text" name="message" placeholder="Type your message..."
                class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600">
            <button type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none">Send</button>
        </form>
    </div>
</x-app-layout>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.3/dist/sweetalert2.all.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const otherUserStatusElement = document.getElementById("other-user-status");
        const chatMessagesContainer = document.getElementById("chat-messages");
        const chatForm = document.getElementById("chat-form");
        const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;
        const chatId = document.getElementById("chat_id").value;
        window.onload = function() {
            const chatMessagesContainer = document.getElementById("chat-messages");

            // Delay scrolling by 100 milliseconds
            setTimeout(function() {
                chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
            }, 100);

            // ...
        };



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
            // Bold the chat from which the notification comes
            boldChatFromNotification(e.chatId); // Use e.chatId
        });

        // ...

        // Function to bold the chat from which the notification comes
        function boldChatFromNotification(chatId) {
            // Find the chat link element in the chat list based on chatId
            const chatLink = chatList.querySelector(`a[data-chat-id="${chatId}"]`);

            if (chatLink) {
                // Add bold styling to the chat link
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
                    // Redirect the user to the chat with the notified chatId
                    window.location.href = "{{ route('chat.show', ['chat' => $userChat->id]) }}"
                }
            });
        }

        // Set up the chat presence channel
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
        if (isOnline) {
            otherUserStatusElement.innerHTML = '<span class="text-green-1000">In chat</span>';
        } else {
            otherUserStatusElement.innerHTML = '<span class="text-gray-1000">Not In chat </span>';
        }
    }



    chatPresenceChannel.listen(".new-chat-message", (e) => {
        const newMessage = e.message;
        console.log(newMessage);
        appendMessageToChatUI(newMessage);

        // Scroll to the newly added message
        const lastMessage = chatMessagesContainer.lastElementChild;
        lastMessage.scrollIntoView({
            behavior: 'smooth'
        });
    });


    // Handle form submission
    chatForm.addEventListener("submit", async (event) => {
        event.preventDefault(); // Prevent the default form submission

        const userInput = document.getElementById("input-message").value;

        try {
            const response = await axios.post("/send-message", {
                _token: csrfToken,
                chat_id: chatId,
                message: userInput,
            });


            document.getElementById("input-message").value = "";

            // Scroll to the bottom of the chat window
            const lastMessage = chatMessagesContainer.lastElementChild;
            lastMessage.scrollIntoView({
                behavior: 'smooth'
            });
        } catch (error) {
            console.error("Error sending message:", error);
        }
    });

    // Function to append a new message to the chat UI
    function appendMessageToChatUI(message) {
        const messageElement = document.createElement("li");
        messageElement.classList.add("flex", "space-x-2");
        if (message.user_id !== userId) {
            messageElement.classList.add("flex-row-reverse");
        }

        const messageBubble = document.createElement("div");
        messageBubble.classList.add("rounded-lg", "p-2", "shadow-md");
        if (message.user_id === userId) {
            messageBubble.classList.add("bg-green-100");
        } else {
            messageBubble.classList.add("bg-white");
        }

        const senderName = document.createElement("span");
        senderName.classList.add("font-semibold");
        if (message.user_id === userId) {
            senderName.classList.add("text-green-800");
        } else {
            senderName.classList.add("text-gray-800");
        }
        senderName.innerText = message.user.name + ":";

        const messageText = document.createElement("p");
        if (message.user_id === userId) {
            messageText.classList.add("text-green-700");
        } else {
            messageText.classList.add("text-gray-700");
        }
        messageText.innerText = message.message;

        messageBubble.appendChild(senderName);
        messageBubble.appendChild(messageText);
        messageElement.appendChild(messageBubble);

        chatMessagesContainer.appendChild(messageElement);

        // Scroll to the newly added message
        const lastMessage = chatMessagesContainer.lastElementChild;
        lastMessage.scrollIntoView({
            behavior: 'smooth'
        });
    }
    });
</script>
