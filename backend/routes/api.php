<?php

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'status' => 'ok',
            'database' => true,
            'timestamp' => now(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'database' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
});




Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [LogoutController::class, 'logout']);


// Verify email
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid verification link'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 200);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json(['message' => 'Email verified successfully']);
    })->middleware(['signed'])->name('verification.verify');

    Route::middleware('auth:sanctum')->group(function () {

    // Resend verification email
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verification link sent!']);
    })->middleware(['throttle:6,1']);
});


Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [ProfileController::class, 'show']);
    Route::put('/user', [ProfileController::class, 'update']);
});
