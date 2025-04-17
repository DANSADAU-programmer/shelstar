<?php

use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
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
    // Potentially a separate route for password update if not handled by Fortify UI
    // Route::put('/password', [UserProfileController::class, 'updatePassword'])->name('password.update');
});