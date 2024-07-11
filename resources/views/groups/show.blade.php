<x-app-layout>
    <x-slot name="header">
        <div class="bg-purple-500 p-4 flex justify-between items-center fixed top-0 left-0 right-0 z-10">
            <h2 class="text-white text-xl font-semibold">{{ $group->name }}</h2>
            <span class="text-xs ml-2" id="other-user-status"></span>
            <button id="copy-invitation-link" class="bg-white text-purple-500 px-4 py-2 rounded-lg hover:bg-gray-100">
                Copy Invitation Link
            </button>
        </div>
    </x-slot>

    <div class="flex h-screen pt-16 overflow-hidden">
        <!-- Chat List (Group Members) -->
        <div class="w-1/4 bg-white dark:bg-gray-800 p-4 overflow-y-auto chat-list">
            <h3 class="text-lg font-semibold mb-2 text-white">Group Members</h3>
            <ul class="space-y-2">
                @foreach ($group->users as $user)
                    <li>
                        <div
                            class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-white">
                            <span id="user-status-{{ $user->id }}"
                                class="flex-shrink-0 w-4 h-4 rounded-full {{ $user->online ? 'bg-purple-500' : 'bg-white' }}"></span>
                            <p>{{ $user->name }}</p>
                            <span id="typing-indicator-{{ $user->id }}" class="text-xs text-gray-500 ml-2"></span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Chat Window (Group Chat) -->
        <div class="flex-grow flex flex-col bg-gray-100 dark:bg-gray-900">
            <div class="flex-grow p-4 overflow-y-auto chat-messages-container" id="chat-messages">
                <ul class="space-y-4">
                    @foreach ($messages as $message)
                        <li class="flex @if ($message->user_id !== $userId) flex-row-reverse @endif">
                            <div
                                class="max-w-xs p-4 rounded-lg @if ($message->user_id === $userId) bg-purple-200 @else bg-white dark:bg-purple-400 @endif shadow-md">
                                <span class="block font-semibold">{{ $message->user->name }}:</span>
                                <p class="mt-1">{{ $message->message }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Chat Input -->
            <div class="bg-white dark:bg-gray-800 p-4 shadow-top">
                <form id="chat-form" class="flex items-center space-x-2">
                    @csrf
                    <input type="hidden" name="group_id" id="group_id" value="{{ $group->id }}">
                    <input id="input-message" type="text" name="message" placeholder="Type your message..."
                        class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500 dark:bg-gray-700 dark:border-gray-600">
                    <button type="submit"
                        class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 focus:outline-none">Send</button>
                </form>
            </div>
        </div>
    </div>
    <input type="hidden" id="invitation-link" value="{{ $invitationLink }}">
</x-app-layout>


<script>
    document.addEventListener("DOMContentLoaded", () => {
        const chatMessagesContainer = document.getElementById("chat-messages");
        const chatForm = document.getElementById("chat-form");
        const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;
        const groupId = document.getElementById("group_id").value;
        const userId = {{ auth()->id() }};
        const userName = "{{ auth()->user()->name }}";

        const copyButton = document.getElementById("copy-invitation-link");
        const invitationLink = document.getElementById("invitation-link").value;

        copyButton.addEventListener("click", () => {
            navigator.clipboard.writeText(invitationLink).then(() => {
                alert("Invitation link copied to clipboard!");
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        });

        const chatPresenceChannel = Echo.join(`group-channel.${groupId}`)
            .here(users => {
                console.log('Users currently in the chat:', users);
                users.forEach(user => updateUserPresence(user.id, true));
            })
            .joining(user => {
                console.log('User joining:', user);
                updateUserPresence(user.id, true);
            })
            .leaving(user => {
                console.log('User leaving:', user);
                updateUserPresence(user.id, false);
            });

        const inputMessage = document.getElementById("input-message");
        inputMessage.addEventListener("input", () => {
            if (inputMessage.value.length === 0) {
                chatPresenceChannel.whisper('stop-typing', {
                    userId
                });
            } else {
                chatPresenceChannel.whisper('typing', {
                    userId
                });
            }
        });

        chatPresenceChannel.listen(".new-group-message", (event) => {
            const newMessage = event.message;
            console.log(newMessage);
            appendMessageToChatUI(newMessage);
        });

        chatPresenceChannel.listenForWhisper('typing', (event) => {
            const {
                userId
            } = event;
            const typingIndicator = document.getElementById(`typing-indicator-${userId}`);
            if (typingIndicator) {
                typingIndicator.textContent = 'typing...';
            }
        });

        chatPresenceChannel.listenForWhisper('stop-typing', (event) => {
            const {
                userId
            } = event;
            const typingIndicator = document.getElementById(`typing-indicator-${userId}`);
            if (typingIndicator) {
                typingIndicator.textContent = '';
            }
        });

        chatForm.addEventListener("submit", async (event) => {
            event.preventDefault();
            const userInput = inputMessage.value.trim();

            try {
                if (userInput) {
                    const response = await axios.post("/send-group-message", {
                        _token: csrfToken,
                        group_id: groupId,
                        message: userInput,
                    });

                    inputMessage.value = '';
                    chatPresenceChannel.whisper('stop-typing', {
                        userId
                    });

                    const lastMessage = chatMessagesContainer.lastElementChild;
                    lastMessage.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            } catch (error) {
                console.error("Error sending message:", error);
            }
        });

        function appendMessageToChatUI(message) {
            const messageElement = document.createElement("li");
            messageElement.classList.add("flex", "items-start", "space-x-2");
            if (message.user_id !== userId) {
                messageElement.classList.add("flex-row-reverse");
            }

            const messageBubble = document.createElement("div");
            messageBubble.classList.add("max-w-xs", "p-4", "rounded-lg", "shadow-md");
            messageBubble.classList.add(message.user_id === userId ? "bg-purple-200" : "bg-white", message
                .user_id === userId ? "text-purple-700" : "text-gray-700");

            const senderName = document.createElement("span");
            senderName.classList.add("block", "font-semibold");
            senderName.textContent = `${message.user.name}:`;

            const messageText = document.createElement("p");
            messageText.classList.add("mt-1");
            messageText.textContent = message.message;

            messageBubble.appendChild(senderName);
            messageBubble.appendChild(messageText);
            messageElement.appendChild(messageBubble);
            chatMessagesContainer.appendChild(messageElement);

            const lastMessage = chatMessagesContainer.lastElementChild;
            lastMessage.scrollIntoView({
                behavior: 'smooth'
            });
        }

        function updateUserPresence(userId, isOnline) {
            const userStatusSpan = document.getElementById(`user-status-${userId}`);
            if (userStatusSpan) {
                userStatusSpan.className =
                    `flex-shrink-0 w-4 h-4 rounded-full ${isOnline ? 'bg-purple-500' : 'bg-white-500'}`;
            }
        }
    });
</script>
