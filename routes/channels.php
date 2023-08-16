<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Broadcast::routes(['middleware' => ['auth:sanctum']]);


// Broadcast::channel('public-chat', function () {
//     return true;
// });
Broadcast::channel('public-chat.{chatId}', function ($user, $chatId) {

    return $user; // Replace with your authorization logic
});
Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return true;
});
