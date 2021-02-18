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

class EducationController extends Controller
{

  public function createEducation(Request $request) {
    $validator = Validator::make($request->all(), [
      'user_id' => 'required|integer|exists:users,id',
      'name' => 'required|string|between:2,100',
      'finish_date' => 'nullable|date',
      'finished' => 'nullable|boolean'
    ]);

    if($validator->fails()){
        return response()->json($validator->errors()->toJson(), 400);
    }

    $user = User::find($request->get('user_id'));
    $author_rank = auth()->user()->userType->rank;
    if( $author_rank >= $user->userType->rank && $author_rank != 1) {
      return response()->json(['message' => 'Unauthorized'],401);
    }

    try {
      $education = Education::create(array_merge($validator->validated()));
    } catch(QueryException $e){
      return response()->json(['message' => 'Education not created'], 422);
    }

    $education = Education::find($education->id);

    return response()->json(($education), 200);

  }

  public function updateEducation(Request $request) {
    $validator = Validator::make($request->all(), [
      'id' => 'required|integer|exists:educations,id',
      'name' => 'required|string|between:2,100',
      'finish_date' => 'nullable|date',
      'finished' => 'nullable|boolean'
    ]);

    if($validator->fails()){
        return response()->json($validator->errors()->toJson(), 400);
    }

    $education = Education::find($request->get('id'));
    $user = User::find($education['user_id']);
    $author_rank = auth()->user()->userType->rank;
    if( $author_rank >= $user->userType->rank && $author_rank != 1) {
      return response()->json(['message' => 'Unauthorized'],401);
    }

    if( $education) {
      try {
        $education->update([
          'name'=> $request->get('name'),
          'finish_date'=> $request->get('finish_date'),
          'finished'=> $request->get('finished'),
        ]);
      } catch(QueryException $e){
        return response()->json(['message' => 'Education not updated'], 422);
      }
    }
    else {
      return response()->json(['message' => 'Education not updated'], 422);

    } 

    return response()->json(($education), 200);

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

  public function deleteEducation($education_id)
  {

    $params = [
      'education_id' => $education_id
    ];

    $validator = Validator::make($params, [
      'education_id' => 'required|integer|exists:educations,id',
    ]);

    if($validator->fails()){
      return response()->json($validator->errors()->toJson(), 400);
    }

    $education = Education::find($education_id);
    $user = User::find($education['user_id']);
    $author_rank = auth()->user()->userType->rank;
    if( $author_rank >= $user->userType->rank && $author_rank != 1) {
      return response()->json(['message' => 'Unauthorized'],401);
    }

    $education->delete();
    return response()->json(null, 204);
  }

}
