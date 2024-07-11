<x-app-layout>
    <x-slot name="header">
        <div class="bg-green-500 p-4 flex justify-between items-center fixed top-0 left-0 right-0 z-10">
            <h2 class="text-white text-xl font-semibold">{{ $group->name }}</h2>
            <span class="text-xs ml-2" id="other-user-status"></span>
            <p>{{ $invitationLink }}</p>
        </div>
    </x-slot>

    <div class="flex h-screen-3/4 overflow-hidden">
        <!-- Chat List (Group Members) -->
        <div class="w-1/4 bg-white dark:bg-gray-800 p-4 overflow-y-auto chat-list">
            <h3 class="text-lg font-semibold mb-2">Group Members</h3>
            <ul class="space-y-2">
                @foreach ($group->users as $user)
                    <li>
                        <div
                            class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <span id="user-status-{{ $user->id }}"
                                class="flex-shrink-0 w-4 h-4 rounded-full {{ $user->online ? 'bg-green-500' : 'bg-gray-500' }}"></span>
                            <p>{{ $user->name }}</p>
                            <span id="typing-indicator-{{ $user->id }}" class="text-xs text-gray-500 ml-2"></span>
                        </div>
                    </li>
                @endforeach

            </ul>
        </div>

        <!-- Chat Window (Group Chat) -->
        <div class="flex-grow p-4 chat-window bg-gray-100 overflow-y-auto">
            <ul class="space-y-4" id="chat-messages">
                @foreach ($messages as $message)
                    <li class="flex items-start space-x-2 @if ($message->user_id !== $userId) flex-row-reverse @endif">
                        <div
                            class="rounded-lg p-2 @if ($message->user_id === $userId) bg-green-100 @else bg-white @endif shadow-md">
                            <p class="@if ($message->user_id === $userId) text-green-700 @else text-gray-700 @endif">
                                <span class="font-semibold">{{ $message->user->name }}:</span>
                                {{ $message->message }}
                            </p>
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
            <input name="id" id="group_id" value="{{ $group->id }}" type="hidden">
            <input id="input-message" type="text" name="message" placeholder="Type your message..."
                class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600">
            <button type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none">Send</button>
        </form>
    </div>
</x-app-layout>


<script>
    document.addEventListener("DOMContentLoaded", () => {
        const chatForm = document.getElementById("chat-form");
        const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;
        const groupId = {{ $group->id }};
        const group = @json($group);

        const inputMessage = document.getElementById("input-message");
        const chatMessagesContainer = document.getElementById("chat-messages");


        @auth
        const userId = {{ auth()->id() }};
        const user = @json(auth()->user());

        const typingIndicator = document.getElementById(`typing-indicator-${userId}`);
        const userName = "{{ auth()->user()->name }}";
        const newMessageNotificationChannel = Echo.private(`notifications.${userId}`);
        const chatPresenceChannel = Echo.join(`group-channel.${groupId}`)
            .here(users => {
                console.log('Users currently in the chat:', users);

                // Update user presence when joining
                users.forEach(user => updateUserPresence(user.id, true));
            })
            .joining(user => {
                console.log('User joining:', user);

                // Update user presence when joining
                updateUserPresence(user.id, true);
            })
            .leaving(user => {
                console.log('User leaving:', user);

                // Update user presence when leaving
                updateUserPresence(user.id, false);
            });

        inputMessage.addEventListener("input", () => {
            if (inputMessage.value.length === 0) {
                chatPresenceChannel.whisper('stop-typing', {
                    userId: userId
                });
            } else {
                chatPresenceChannel.whisper('typing', {
                    userId: userId
                })
            };
        });
    @endauth

    chatPresenceChannel.listen(".new-group-message", (e) => {
        const newMessage = e.message;
        console.log(newMessage);
        appendMessageToChatUI(newMessage);

        // Scroll to the newly added message
        const lastMessage = chatMessagesContainer.lastElementChild;
        lastMessage.scrollIntoView({
            behavior: 'smooth'
        });
    }); chatPresenceChannel.listenForWhisper('typing', (e) => {
        const userIdTyping = e.userId;
        const typingIndicator = document.getElementById(`typing-indicator-${userIdTyping}`);
        typingIndicator.textContent = ` typing...`;
    });

    chatPresenceChannel.listenForWhisper('stop-typing', (e) => {
        const userIdStoppedTyping = e.userId;
        const typingIndicator = document.getElementById(`typing-indicator-${userIdStoppedTyping}`);
        typingIndicator.textContent = ''; // Clear the typing indicator
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

        const chatMessagesContainer = document.getElementById("chat-messages");
        chatMessagesContainer.appendChild(messageElement);

        // Scroll to the newly added message
        const lastMessage = chatMessagesContainer.lastElementChild;
        lastMessage.scrollIntoView({
            behavior: 'smooth'
        });
    }

    function updateUserPresence(userId, isOnline) {
        const userStatusSpan = document.getElementById(`user-status-${userId}`);
        if (userStatusSpan) {
            userStatusSpan.className =
                `flex-shrink-0 w-4 h-4 rounded-full ${isOnline ? 'bg-green-500' : 'bg-gray-500'}`;
        }
    }

    chatForm.addEventListener("submit", async (event) => {
        event.preventDefault(); // Prevent the default form submission

        const userInput = document.getElementById("input-message").value;

        try {
            const response = await axios.post("/send-group-message", {
                _token: csrfToken,
                group_id: groupId, // Include the correct group_id for group messages
                message: userInput,
            });

            document.getElementById("input-message").value = "";

            chatPresenceChannel.whisper('stop-typing', {
                userId: userId
            });

            // Scroll to the bottom of the chat window
            const chatMessagesContainer = document.getElementById("chat-messages");
            const lastMessage = chatMessagesContainer.lastElementChild;
            lastMessage.scrollIntoView({
                behavior: 'smooth'
            });
        } catch (error) {
            console.error("Error sending message:", error);
        }
    });
    })
</script>
