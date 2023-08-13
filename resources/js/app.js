import "./bootstrap";
// document.addEventListener("DOMContentLoaded", () => {
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
document.addEventListener("DOMContentLoaded", () => {
    const chatChannel = Echo.channel("public-chat");
    const chatMessages = []; // Array to store chat messages

    chatChannel.listen(".new-chat-message", (e) => {
        const newMessage = e.message;

        // Add the new message to the chatMessages array
        chatMessages.push(newMessage);

        // Update the UI with all messages in the chatMessages array
        updateChatUI();
    });

    const chatForm = document.getElementById("chat-form");
    chatForm.addEventListener("submit", async (event) => {
        event.preventDefault();
        const userInput = document.getElementById("input-message").value;
        const csrfToken = document.head.querySelector(
            'meta[name="csrf-token"]'
        ).content;

        const chatId = document.getElementById("chat_id").value;

        try {
            const response = await axios.post("/send-message", {
                _token: csrfToken,
                chat_id: chatId,
                message: userInput,
            });

            console.log("Message sent:", response.data.message);
            document.getElementById("input-message").value = "";
        } catch (error) {
            console.error("Error sending message:", error);
        }
    });

    // Function to update the chat UI based on the chatMessages array
    function updateChatUI() {
        const chatWindow = document.querySelector(".chat-window");
        chatWindow.innerHTML = ""; // Clear existing messages

        chatMessages.forEach((message) => {
            const messageElement = document.createElement("li");
            messageElement.innerHTML = `
                <span class="font-semibold">${message.user.name}:</span>
                <p>${message.message}</p>
            `;
            chatWindow.appendChild(messageElement);
        });
    }
});

// const chatChannel = Echo.channel("public-chat");

// chatChannel
//     .subscribed(() => {
//         console.log("sub");
//     })
//     .listen(".new-chat-message", (e) => {
//         console.log("Received message:", e);
//         const newMessage = e.message;
//         const chatWindow = document.querySelector(".chat-window");
//         const messageElement = document.createElement("li");
//         messageElement.innerHTML = `
//     <span class="font-semibold">${newMessage.user.name}:</span>
//     <p>${newMessage.message}</p>
// `;
//         chatWindow.append(messageElement);
//     });
// document
//     .getElementById("chat-form")
//     .addEventListener("submit", function (event) {
//         event.preventDefault();
//         const userInput = document.getElementById("input-message").value;
//         const csrfToken = document.head.querySelector(
//             'meta[name="csrf-token"]'
//         ).content;

//         const chatId = document.getElementById("chat_id").value; // Make sure your input field has the correct ID

//         axios
//             .post("/send-message", {
//                 _token: csrfToken,
//                 chat_id: chatId,
//                 message: userInput,
//             })
//             .then((response) => {
//                 console.log("Message sent:", response.data.message);
//             });

//         document.getElementById("input-message").value = "";
//     });

// import Alpine from "alpinejs";

// window.Alpine = Alpine;

// Alpine.start();
