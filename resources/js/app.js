import "./bootstrap";
//     const chatChannel = Echo.channel("public-chat");

//     chatChannel.listen(".new-chat-message", (e) => {
//         const newMessage = e.message;

//         // Create a new message element
//         const messageElement = document.createElement("li");
//         messageElement.innerHTML = `
//             <span class="font-semibold">${newMessage.user.name}:</span>
//             <p>${newMessage.message}</p>
//         `;

//         // Append the new message to the chat window
//         const chatWindow = document.querySelector(".chat-window");
//         chatWindow.appendChild(messageElement);
//     });

//     const chatForm = document.getElementById("chat-form");
//     chatForm.addEventListener("submit", async (event) => {
//         event.preventDefault();
//         const userInput = document.getElementById("input-message").value;
//         const csrfToken = document.head.querySelector(
//             'meta[name="csrf-token"]'
//         ).content;

//         const chatId = document.getElementById("chat_id").value;

//         try {
//             const response = await axios.post("/send-message", {
//                 _token: csrfToken,
//                 chat_id: chatId,
//                 message: userInput,
//             });

//             console.log("Message sent:", response.data.message);
//             document.getElementById("input-message").value = "";
//         } catch (error) {
//             console.error("Error sending message:", error);
//         }
//     });
// });

// document.addEventListener("DOMContentLoaded", () => {
//     const chatChannel = Echo.channel("public-chat");
//     const chatMessages = []; // Array to store chat messages

//     chatChannel.listen(".new-chat-message", (e) => {
//         const newMessage = e.message;

//         // Add the new message to the chatMessages array
//         chatMessages.push(newMessage);

//         // Update the UI with the new message
//         appendMessageToChatUI(newMessage);
//     });

//     const chatForm = document.getElementById("chat-form");
//     chatForm.addEventListener("submit", async (event) => {
//         event.preventDefault();
//         const userInput = document.getElementById("input-message").value;
//         const csrfToken = document.head.querySelector(
//             'meta[name="csrf-token"]'
//         ).content;

//         const chatId = document.getElementById("chat_id").value;

//         try {
//             const response = await axios.post("/send-message", {
//                 _token: csrfToken,
//                 chat_id: chatId,
//                 message: userInput,
//             });

//             console.log("Message sent:", response.data.message);
//             document.getElementById("input-message").value = "";
//         } catch (error) {
//             console.error("Error sending message:", error);
//         }
//     });

//     // Function to append a new message to the chat UI
//     function appendMessageToChatUI(message) {
//         const chatMessagesContainer = document.getElementById("chat-messages");

//         const messageElement = document.createElement("li");
//         messageElement.classList.add("flex", "space-x-2");

//         const messageBubble = document.createElement("div");
//         messageBubble.classList.add(
//             "rounded-lg",
//             "p-2",
//             "bg-white",
//             "shadow-md"
//         );

//         const senderName = document.createElement("span");
//         senderName.classList.add("font-semibold", "text-gray-800");
//         senderName.innerText = message.user.name + ":";

//         const messageText = document.createElement("p");
//         messageText.classList.add("text-gray-700");
//         messageText.innerText = message.message;

//         messageBubble.appendChild(senderName);
//         messageBubble.appendChild(messageText);
//         messageElement.appendChild(messageBubble);

//         chatMessagesContainer.appendChild(messageElement);

//         // Scroll to the bottom of the chat window
//         chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
//     }
// });

// import Alpine from "alpinejs";

// window.Alpine = Alpine;

// Alpine.start();
