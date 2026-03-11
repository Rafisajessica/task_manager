<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

// Routes publiques
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Tâches
    Route::apiResource('tasks', TaskController::class);

    // Projets
    Route::apiResource('projects', ProjectController::class);

    // Commentaires
    Route::post('tasks/{task}/comments', [CommentController::class, 'store']);
    Route::delete('comments/{comment}',  [CommentController::class, 'destroy']);

    // Catégories
    Route::apiResource('categories', CategoryController::class);

    // Chat
    Route::post('/chat',         [ChatController::class, 'send']);
    Route::get('/chat/history',  [ChatController::class, 'history']);

    // Dashboard
    Route::get('/dashboard',         [DashboardController::class, 'index']);

    // Notifications
    Route::get('/notifications',           [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    //users
    Route::get('/users',       [UserController::class, 'index']);
    Route::put('/users/{user}',   [UserController::class, 'update']);
    Route::delete('/users/{user}',[UserController::class, 'destroy']);

   

// Dans le groupe auth:sanctum
Route::get('/tasks/{task}/comments',    [CommentController::class, 'index']);
Route::post('/tasks/{task}/comments',   [CommentController::class, 'store']);
Route::delete('/comments/{comment}',    [CommentController::class, 'destroy']);

//Notifications
Route::get('/notifications',              [NotificationController::class, 'index']);
Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
Route::put('/notifications/{id}/read',    [NotificationController::class, 'markRead']);
Route::put('/notifications/read-all',     [NotificationController::class, 'markAllRead']);

});