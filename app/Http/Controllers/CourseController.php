<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\models\DrivingLicence;
use App\Models\UserType;
use App\Models\Education;
use App\Models\Language;
use App\Notifications\CustomNewUserNotification;
use Illuminate\Database\QueryException;
use Validator;

class CourseController extends Controller
{

  public function createOrUpdateCourse(Request $request) {
    $validator = Validator::make($request->all(), [
      'user_id' => 'required|integer|exists:users,id',
      'course_id' => 'required|integer|exists:courses,id',
      'number' => 'nullable|string|between:2,100',
      'expedition_date' => 'nullable|date',
      'valid_until' => 'nullable|date'
    ]);

    if($validator->fails()){
        return response()->json($validator->errors()->toJson(), 400);
    }

    $user = User::find($request->get('user_id'));
    $author_rank = auth()->user()->userType->rank;
    if( $author_rank >= $user->userType->rank && $author_rank != 1) {
      return response()->json(['message' => 'Unauthorized'],401);
    }

    $course = $user->courses()->find($request->get('course_id'));
    if( !$course) {
      try {
        $user->courses()->attach($request->get('course_id'),[
          'number'=> $request->get('number'),
          'expedition_date' => $request->get('expedition_date'),
          'valid_until' => $request->get('valid_until'),
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ]);
      } catch(QueryException $e){
        return response()->json(['message' => 'Course not created'], 422);
      }
    }
    else {
      try {
        $user->courses()->updateExistingPivot($request->get('course_id'),[
          'number'=> ($request->get('number')?$request->get('number'):$course->pivot->number),
          'expedition_date' => ($request->get('expedition_date')?$request->get('expedition_date'):$course->pivot->expedition_date),
          'valid_until' => ($request->get('valid_until')?$request->get('valid_until'):$course->pivot->valid_until),
          'updated_at' => Carbon::now()
        ]);
      } catch(QueryException $e){
        return response()->json(['message' => 'Course not updated'], 422);
      }
    }

    $response_course = $user->courses()->find($request->get('course_id'));
    $response_course['number'] = $response_course->pivot->number; 
    $response_course['expedition_date'] = $response_course->pivot->expedition_date; 
    $response_course['valid_until'] = $response_course->pivot->valid_until; 

    return response()->json(($response_course), 200);

  }

  // public function updateCourse(Request $request)
  // {

  //   var_dump($request->all());

  //   $validator = Validator::make($request->all(), [
  //     'user_id' => 'required|integer|exists:users,id',
  //     'course_id' => 'required|integer|exists:courses,id',
  //     'number' => 'nullable|string|between:2,100',
  //     'expedition_date' => 'nullable|date',
  //     'valid_until' => 'nullable|date'
  //   ]);

  //   var_dump($request->get('number'));

  //   if($validator->fails()){
  //       return response()->json($validator->errors()->toJson(), 400);
  //   }

  //   $user = User::find($request->get('user_id'));
  //   $author_rank = auth()->user()->userType->rank;
  //   if( $author_rank >= $user->userType->rank && $author_rank != 1) {
  //     return response()->json(['message' => 'Unauthorized'],401);
  //   }

  //   try {
  //     $user->courses()->updateExistingPivot($request->get('course_id'),[
  //       'number'=> $request->get('number'),
  //       'expedition_date' => $request->get('expedition_date'),
  //       'valid_until' => $request->get('valid_until'),
  //       'updated_at' => Carbon::now()
  //     ]);
  //   } catch(QueryException $e){
  //     return response()->json(['message' => 'Course not updated'], 422);
  //   }

  //   $updated_course = $user->courses()->find($request->get('course_id'));
  //   $updated_course['number'] = $updated_course->pivot->number; 
  //   $updated_course['expedition_date'] = $updated_course->pivot->expedition_date; 
  //   $updated_course['valid_until'] = $updated_course->pivot->valid_until; 

  //   return response()->json(($updated_course), 201);

  // }

  public function deleteCourse(Request $request)
  {

    $params = [
      'user_id' => $request->route('user_id'),
      'course_id' => $request->route('course_id')
    ];

    $validator = Validator::make($params, [
      'user_id' => 'required|integer|exists:users,id',
      'course_id' => 'required|integer|exists:courses,id',
    ]);

    if($validator->fails()){
      return response()->json($validator->errors()->toJson(), 400);
    }

    $user = User::find($params['user_id']);
    $author_rank = auth()->user()->userType->rank;
    if( $author_rank >= $user->userType->rank && $author_rank != 1) {
      return response()->json(['message' => 'Unauthorized'],401);
    }

    $user->courses()->detach($params['course_id']);
    return response()->json(null, 204);
  }

}
