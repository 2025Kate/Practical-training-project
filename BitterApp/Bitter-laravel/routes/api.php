<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::get('/test', function () {
    return response()->json(['api' => 'test works from api.php']);
});

// Убедитесь, что эти маршруты присутствуют:
Route::get('/posts', [PostController::class, 'index']);
Route::post('/posts', [PostController::class, 'store']);
Route::get('/posts/author/{username}', [PostController::class, 'byAuthor']); // ← этот маршрут
Route::get('/posts/hashtag/{tag}', [PostController::class, 'byHashtag']);
Route::get('/test-connection', [PostController::class, 'testConnection']);