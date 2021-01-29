<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

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


  Route::prefix('auth')->middleware('api')->group( function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/profile', [AuthController::class, 'userProfile']);    
      
         
  });
  Route::get('/auth/email/verify', [AuthController::class, 'sendVerifyEmail'])->middleware('auth')->name('verification.notice');
  Route::get('/auth/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware(['signed'])->name('verification.verify'); 