<?php

use App\Events\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\ExampleController;

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

Route::get('/', [UserController::class, "showCorrectHomepage"])->name('login');

// Auth routes
Route::post('/register', [UserController::class, "register"])->middleware('guest');
Route::post('/login', [UserController::class, "login"])->middleware('guest');
Route::post('/logout', [UserController::class, "logout"])->middleware('mustBeLoggedIn');

// Blog-related routes
Route::get('/create-post', [PostController::class, 'showCreateForm'])->middleware('mustBeLoggedIn');
Route::post('/create-post', [PostController::class, 'createPost'])->middleware('mustBeLoggedIn');
Route::get('/post/{post}', [PostController::class, 'viewPost']);
Route::delete('/post/{post}', [PostController::class, 'deletePost'])->middleware('can:delete,post');
Route::get('/post/{post}/edit', [PostController::class, 'showEditForm'])->middleware('can:update,post');
Route::put('/post/{post}', [PostController::class, 'updateForm'])->middleware('can:update,post');

// Search
Route::get('/search/{term}', [PostController::class, 'search']);

// Profile-related routes
Route::get('/profile/{user:username}', [UserController::class, 'viewProfile']);
Route::get('/profile/{user:username}/followers', [UserController::class, 'viewProfileFollowers']);
Route::get('/profile/{user:username}/following', [UserController::class, 'viewProfileFollowing']);

// SPA
Route::middleware('cache.headers:public;max_age=20;etag')->group(function () {
    Route::get('/profile/{user:username}/raw', [UserController::class, 'viewProfileRaw'])->middleware('cache.headers:public;max_age=20;etag');
    Route::get('/profile/{user:username}/followers/raw', [UserController::class, 'viewProfileFollowersRaw']);
    Route::get('/profile/{user:username}/following/raw', [UserController::class, 'viewProfileFollowingRaw']);
});

Route::get('/manage-avatar', [UserController::class, 'showAvatarForm'])->middleware('mustBeLoggedIn');
Route::post('/manage-avatar', [UserController::class, 'updateAvatar'])->middleware('mustBeLoggedIn');


Route::get('/admins-exclusive', function () {
    // if (Gate::allows('visitAdminPages')) {
    //     return 'Only admins can access this page!';
    // };
    return 'Only admins (like you!) can access this page!';
})->middleware('can:visitAdminPages');

// Follow-routes
Route::post('/create-follow/{user:username}', [FollowController::class, 'createFollow'])->middleware('mustBeLoggedIn');
Route::post('/remove-follow/{user:username}', [FollowController::class, 'removeFollow'])->middleware('mustBeLoggedIn');

// Chat route
Route::post('/send-chat-message', function (Request $request) {
    $formFields = $request->validate([
        'textvalue' => 'required'
    ]);
    if (!trim(strip_tags($formFields['textvalue']))) {
        return response()->noContent();
    }

    broadcast(new ChatMessage(['username' => auth()->user()->username, 'textvalue' => strip_tags($request->textvalue), 'avatar' => auth()->user()->avatar]))->toOthers();
    return response()->noContent();
})->middleware('mustBeLoggedIn');
