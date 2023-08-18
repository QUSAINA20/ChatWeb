<?php


use App\Http\Controllers\ChatController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ProfileController;
use App\Models\Group;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::post('/test', function () {
    return "hi";
});
Route::get('/', function () {
    return view('welcome');
});
Route::middleware(['auth'])->group(function () {
    Route::get('/chats/{chat}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/send-message', [ChatController::class, 'sendMessage']);

    Route::post('/chat/create', [ChatController::class, 'createChat'])->name('create.chat');
    Route::get('/chat/create', [ChatController::class, 'create']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/groups', [GroupController::class, 'listGroups'])->name('group.list');
    Route::get('/groups/create', [GroupController::class, 'create'])->name('group.create');
    Route::get('/groups/{group}', [GroupController::class, 'show'])->name('group.show');
    Route::post('/groups', [GroupController::class, 'store'])->name('group.store');
    Route::post('/send-group-message', [GroupController::class, 'sendMessage']);
    Route::get('/groups/{group}/join/{token}', [GroupController::class, 'joinGroupByToken'])->name('group.join');
    Route::post('/groups/{group}/admin/{user}', [GroupController::class, 'updateAdmin'])->name('group.admin.update');
    Route::get('/groups/{group}/add-users', [GroupController::class, 'addUserToGroupForm'])->name('group.add-users');
    Route::post('/groups/{group}/add-users', [GroupController::class, 'addUserToGroup'])->name('group.add-users.post');
    Route::post('/groups/{group}/remove-user/{user}', [GroupController::class, 'removeUserFromGroup'])->name('group.users.remove');
});



Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



require __DIR__ . '/auth.php';
