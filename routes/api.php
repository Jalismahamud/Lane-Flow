<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ContactUsController;
use App\Http\Controllers\Api\Auth\SocialAuthController;
use App\Http\Controllers\Api\Auth\UserProfileController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\AuthenticationController;
use App\Http\Controllers\Web\Backend\Settings\DynamicPageController;



Route::get('privacy-policy', [DynamicPageController::class, 'privacyPolicy']);
Route::get('terms-and-conditions', [DynamicPageController::class, 'termsAndConditions']);
Route::post('/contact-us', [ContactUsController::class, 'contactUs']);



Route::post('google-authentication', [SocialAuthController::class, 'googleAuthentication']);
Route::post('apple-authentication', [SocialAuthController::class, 'appleAuthentication']);



Route::group(['middleware' => 'guest:api',], function () {
    Route::post('/login', [AuthenticationController::class, 'login']);
    Route::post('/register', [AuthenticationController::class, 'register']);
    Route::post('/register-otp-verify', [AuthenticationController::class, 'registrationVerifyOtp']);
    Route::post('/forgot-password', [ResetPasswordController::class, 'forgotPassword']);
    Route::post('/resend-otp', [ResetPasswordController::class, 'resendOtp']);
    Route::post('/verify-otp', [ResetPasswordController::class, 'VerifyOTP']);
    Route::post('/reset-password', [ResetPasswordController::class, 'ResetPassword']);
});



Route::group(['middleware' => ['auth:api']], function () {

    Route::get('/profile', [UserProfileController::class, 'profile']);
    Route::post('/update-profile', [UserProfileController::class, 'updateProfile']);
    Route::post('/update-avatar', [UserProfileController::class, 'updateAvatar']);
    Route::post('/update-password', [UserProfileController::class, 'updatePassword']);

    Route::delete('/delete-profile', [UserProfileController::class, 'deleteProfile']);
    Route::post('/logout', [AuthenticationController::class, 'logout']);


});
