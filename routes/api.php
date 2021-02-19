<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ConstantsController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\LanguageController;

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
Route::post('/user/delete/{id}', [UserController::class, 'delete'])->middleware('auth');

/* course */
Route::post('/course', [CourseController::class, 'createCourse'])->middleware('auth');
Route::put('/course', [CourseController::class, 'updateCourse'])->middleware('auth');
Route::delete('/course/{user_id}/{course_id}', [CourseController::class, 'deleteCourse'])->middleware('auth');

/* education */
Route::post('/education', [EducationController::class, 'createEducation'])->middleware('auth');
Route::put('/education', [EducationController::class, 'updateEducation'])->middleware('auth');
Route::delete('/education/{education_id}', [EducationController::class, 'deleteEducation'])->middleware('auth');

/* language */
Route::post('/language', [LanguageController::class, 'createLanguage'])->middleware('auth');
Route::put('/language', [LanguageController::class, 'updateLanguage'])->middleware('auth');
Route::delete('/language/{language_id}', [LanguageController::class, 'deleteLanguage'])->middleware('auth');

/* constants */
Route::get('/constants/usertypes', [ConstantsController::class, 'userTypes'])->middleware('auth');
Route::get('/constants/coursetypes', [ConstantsController::class, 'courseTypes'])->middleware('auth');

/* email */
Route::get('/email/verify', [VerifyEmailController::class, 'sendVerifyEmail'])->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verifyEmail'])->middleware('guest')->name('verification.verify'); 

/* forgot password */
Route::post('/password/forgot', [ForgotPasswordController::class, 'sendForgotPasswordEmail'])->middleware('guest')->name('password.request');
Route::post('/password/reset', [ForgotPasswordController::class, 'passwordReset'])->middleware('guest')->name('password.update');