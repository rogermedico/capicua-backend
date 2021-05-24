<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Education;
use Validator;

class EducationController extends Controller
{

    public function createEducation(Request $request) 
    {
      
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'finish_date' => 'nullable|date',
            'finished' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $education = Education::create(array_merge(
            $validator->validated(),
            ['user_id' => auth()->user()->id]
        ));

        $education = Education::find($education->id);

        return response()->json($education, 200);

    }

    public function updateEducation(Request $request) 
    {
      
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:educations,id',
            'name' => 'required|string|between:2,100',
            'finish_date' => 'nullable|date',
            'finished' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $education = Education::find($validated_data['id']);

        if(!$education) {
            return response()->json(['message' => 'Education not found'], 422);
        }

        if($education->user_id != auth()->user()->id){
            return response()->json(['message' => 'Unauthorized'],401);
        }

        $education->update([
            'name'=> $validated_data['name'],
            'finish_date'=> $validated_data['finish_date'],
            'finished'=> $validated_data['finished'],
        ]);

        return response()->json(($education), 200);

    }

  public function deleteEducation($education_id)
  {

      $params = [
          'education_id' => $education_id
      ];

      $validator = Validator::make($params, [
          'education_id' => 'required|integer|exists:educations,id',
      ]);

      if ($validator->fails()) {
          return response()->json(['error' => $validator->errors()->first()], 400);
      }

      $validated_data = $validator->valid();

      $education = Education::find($validated_data['education_id']);
      
      if($education->user_id != auth()->user()->id){
          return response()->json(['message' => 'Unauthorized'],401);
      }

      $education->delete();

      return response()->json(null, 204);

  }

}
