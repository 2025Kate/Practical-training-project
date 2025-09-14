<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

// Web маршруты
Route::get('/test-basic', function () {
    return 'Basic test works!';
});

Route::get('/', function () {
    return response()->json([
        'message' => 'Bitter API',
        'version' => '1.0'
    ]);
});