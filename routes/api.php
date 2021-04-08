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
use App\Http\Controllers\PersonalDocumentsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HomeDocumentsController;

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
Route::get('/auth/refresh', [AuthController::class, 'refresh'])->middleware('auth');

/* user */
Route::post('/user/password', [UserController::class, 'changePassword'])->middleware('auth');
Route::post('/user/password/check', [UserController::class, 'checkPassword'])->middleware('auth');

Route::post('/user/avatar', [UserController::class, 'setUserAvatar'])->middleware('auth');
Route::get('/user/avatar/{id}', [UserController::class, 'getUserAvatar'])->middleware('auth');
Route::delete('/user/avatar', [UserController::class, 'deleteUserAvatar'])->middleware('auth');

Route::post('/user/dni', [UserController::class, 'setUserDni'])->middleware('auth');
Route::get('/user/dni/{id}', [UserController::class, 'getUserDni'])->middleware('auth');
Route::delete('/user/dni', [UserController::class, 'deleteUserDni'])->middleware('auth');

Route::post('/user/offenses', [UserController::class, 'setUserOffenses'])->middleware('auth');
Route::get('/user/offenses/{id}', [UserController::class, 'getUserOffenses'])->middleware('auth');
Route::delete('/user/offenses', [UserController::class, 'deleteUserOffenses'])->middleware('auth');

Route::post('/user/activate', [UserController::class, 'activate'])->middleware('auth');
Route::post('/user/deactivate', [UserController::class, 'deactivate'])->middleware('auth');
Route::delete('/user/{user_id}', [UserController::class, 'delete'])->middleware('auth');

Route::get('/user/{id}', [UserController::class, 'getUserById'])->middleware('auth');
Route::put('/user', [UserController::class, 'updateProfile'])->middleware('auth');
Route::get('/user', [UserController::class, 'user'])->middleware('auth');
Route::post('/user', [UserController::class, 'create'])->middleware('auth');

/* users */
Route::get('/users', [UserController::class, 'users'])->middleware('auth');
Route::put('/users', [UserController::class,'editUser'])->middleware('auth');

/* course */
Route::post('/course', [CourseController::class, 'createCourse'])->middleware('auth');
Route::put('/course', [CourseController::class, 'updateCourse'])->middleware('auth');
Route::delete('/course/{course_id}', [CourseController::class, 'deleteCourse'])->middleware('auth');

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

/* personal documents */
Route::get('/documents/info',[PersonalDocumentsController::class,'getAllDocumentsInfo'])->middleware('auth');
Route::get('/documents/info/{user_id}',[PersonalDocumentsController::class,'getPersonalDocumentsInfo'])->middleware('auth');
Route::get('/documents/{id}',[PersonalDocumentsController::class,'getPersonalDocument'])->middleware('auth');
Route::post('/documents',[PersonalDocumentsController::class,'createPersonalDocument'])->middleware('auth');
Route::delete('/documents/{id}',[PersonalDocumentsController::class,'deletePersonalDocument'])->middleware('auth');

/* home */
Route::get('/home',[HomeController::class,'getAllHomePosts'])->middleware('auth');
Route::get('/home/{id}',[HomeController::class,'getHomePost'])->middleware('auth');
Route::post('/home',[HomeController::class,'createHomePost'])->middleware('auth');
Route::put('/home/{id}',[HomeController::class,'updateHomePost'])->middleware('auth');
Route::delete('/home/{id}',[HomeController::class,'deleteHomePost'])->middleware('auth');

/* home documents */
Route::get('/homedocument/{id}',[HomeDocumentsController::class,'getHomeDocument'])->middleware('auth');
Route::post('/homedocument',[HomeDocumentsController::class,'createHomeDocument'])->middleware('auth');
Route::delete('/homedocument/{id}',[HomeDocumentsController::class,'deleteHomeDocument'])->middleware('auth');

/* email */
Route::get('/email/verify', [VerifyEmailController::class, 'sendVerifyEmail'])->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verifyEmail'])->middleware('guest')->name('verification.verify'); 

/* forgot password */
Route::post('/password/forgot', [ForgotPasswordController::class, 'sendForgotPasswordEmail'])->middleware('guest')->name('password.request');
Route::post('/password/reset', [ForgotPasswordController::class, 'passwordReset'])->middleware('guest')->name('password.update');
