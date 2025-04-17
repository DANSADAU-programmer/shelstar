<?php

use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
class User extends Authenticatable
{
    use HasApiTokens;
}

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Protected routes (require authentication via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/user', [AuthController::class, 'user'])->name('user');
    Route::get('/profile', [UserProfileController::class, 'getProfile'])->name('profile.get');
    Route::put('/profile', [UserProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/settings', [UserProfileController::class, 'updateSettings'])->name('settings.update');
    Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
    Route::put('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::put('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::post('/payment/initiate', [PaymentController::class, 'initiate'])->name('payment.initiate');
    Route::get('/payment/verify/{reference}', [PaymentController::class, 'verify'])->name('payment.verify');
    Route::post('/payment/webhook/{gateway}', [PaymentController::class, 'webhook'])->name('payment.webhook');
    // Potentially a separate route for password update if not handled by Fortify UI
    // Route::put('/password', [UserProfileController::class, 'updatePassword'])->name('password.update');
});

Route::prefix('conversations')->group(function () {
    Route::get('/', [MessageController::class, 'index'])->name('conversations.index'); // Get user's conversations
    Route::get('/{conversation}', [MessageController::class, 'show'])->name('conversations.show'); // Get messages in a conversation
    Route::post('/{agent}', [MessageController::class, 'store'])->name('conversations.store'); // Start a new conversation or send a message
    Route::put('/{conversation}/read', [MessageController::class, 'markAsRead'])->name('conversations.read'); // Mark messages as read
});