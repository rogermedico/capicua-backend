<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\ForgotPasswordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });



// Route::apiResource('/users',UserController::class);
// Route::middleware('auth:api')->get('/users', [UserController::class, 'index']);
// Route::middleware('auth:api')->get('/users/{user}', [UserController::class, 'show']);
// Route::middleware('auth:api')->post('/users', [UserController::class, 'store']);


//just for test
// Route::get('/users', [UserController::class, 'index']);
// Route::get('/users/{user}', [UserController::class, 'show']);
// Route::post('/users', [UserController::class, 'store']);

// Route::group([
//   'middleware' => 'api',
//   'prefix' => 'auth'

// ], function ($router) {
//   Route::post('/login', [AuthController::class, 'login']);
//   Route::post('/register', [AuthController::class, 'register']);
//   Route::post('/logout', [AuthController::class, 'logout']);
//   Route::post('/refresh', [AuthController::class, 'refresh']);
//   Route::get('/profile', [AuthController::class, 'userProfile']);    
//   Route::get('/email/verify', [AuthController::class, 'sendVerifyEmail'])->name('verification.notice');  
//   Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');      
// });

/* auth */
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('auth');
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::post('/auth/refresh', [AuthController::class, 'refresh'])->middleware('auth');

/* user */
Route::get('/user', [UserController::class, 'user'])->middleware('auth');
Route::get('/user/{id}', [UserController::class, 'getUserById'])->middleware('auth');
Route::post('/user', [UserController::class, 'create'])->middleware('auth');
Route::get('/users', [UserController::class, 'users'])->middleware('auth');
Route::post('/user/{id}', [UserController::class, 'update'])->middleware('auth');
Route::post('/user/password', [UserController::class, 'changePassword'])->middleware('auth');
Route::post('/user/password/check', [UserController::class, 'checkPassword'])->middleware('auth');

/* email */
Route::get('/email/verify', [VerifyEmailController::class, 'sendVerifyEmail'])->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verifyEmail'])->middleware('guest')->name('verification.verify'); 

/* forgot password */
Route::post('/password/forgot', [ForgotPasswordController::class, 'sendForgotPasswordEmail'])->middleware('guest')->name('password.request');
Route::post('/password/reset', [ForgotPasswordController::class, 'passwordReset'])->middleware('guest')->name('password.update');