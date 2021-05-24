<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Validator;

class CourseController extends Controller
{

  public function createCourse(Request $request) 
  {
      $validator = Validator::make($request->all(), [
          'course_id' => 'required|integer|exists:courses,id',
          'number' => 'nullable|string|between:2,100',
          'expedition_date' => 'nullable|date',
          'valid_until' => 'nullable|date'
      ]);

      if ($validator->fails()) {
          return response()->json(['error' => $validator->errors()->first()], 400);
      }

      $validated_data = $validator->valid();

      $course = auth()->user()->courses()->find($validated_data['course_id']);
      if(!$course) {
          auth()->user()->courses()->attach(
              $validated_data['course_id'],
              [
                  'number'=> $validated_data['number'],
                  'expedition_date' => $validated_data['expedition_date'],
                  'valid_until' => $validated_data['valid_until'],
                  'created_at' => Carbon::now(),
                  'updated_at' => Carbon::now()
              ]
          );
      }
      else {
          return response()->json(['message' => 'Course not created'], 422);
      }

      $response_course = auth()->user()->courses()->find($validated_data['course_id']);
      $response_course['number'] = $response_course->pivot->number; 
      $response_course['expedition_date'] = $response_course->pivot->expedition_date; 
      $response_course['valid_until'] = $response_course->pivot->valid_until; 

      return response()->json(($response_course), 200);

  }

  public function updateCourse(Request $request) 
  {
      $validator = Validator::make($request->all(), [
          'course_id' => 'required|integer|exists:courses,id',
          'number' => 'nullable|string|between:2,100',
          'expedition_date' => 'nullable|date',
          'valid_until' => 'nullable|date'
      ]);

      if ($validator->fails()) {
          return response()->json(['error' => $validator->errors()->first()], 400);
      }

      $validated_data = $validator->valid();

      $course = auth()->user()->courses()->find($validated_data['course_id']);
      if($course) {
          auth()->user()->courses()->updateExistingPivot(
              $validated_data['course_id'],
              [
                  'number'=> ($validated_data['number']?$validated_data['number']:$course->pivot->number),
                  'expedition_date' => ($validated_data['expedition_date']?$validated_data['expedition_date']:$course->pivot->expedition_date),
                  'valid_until' => ($validated_data['valid_until']?$validated_data['valid_until']:$course->pivot->valid_until),
                  'updated_at' => Carbon::now()
              ]
          );
      }
      else {
          return response()->json(['message' => 'Course not updated'], 422);
      }

      $response_course = auth()->user()->courses()->find($validated_data['course_id']);
      $response_course['number'] = $response_course->pivot->number; 
      $response_course['expedition_date'] = $response_course->pivot->expedition_date; 
      $response_course['valid_until'] = $response_course->pivot->valid_until; 

      return response()->json(($response_course), 200);

  }

  

  public function deleteCourse(Request $request)
  {

      $params = [
          'course_id' => $request->route('course_id')
      ];

      $validator = Validator::make($params, [
          'course_id' => 'required|integer|exists:courses,id',
      ]);

      if ($validator->fails()) {
          return response()->json(['error' => $validator->errors()->first()], 400);
      }

      $validated_data = $validator->valid();

      auth()->user()->courses()->detach($validated_data['course_id']);

      return response()->json(null, 204);
      
  }

}
