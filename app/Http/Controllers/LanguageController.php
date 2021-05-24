<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Language;
use Validator;

class LanguageController extends Controller
{

    public function createLanguage(Request $request) 
    {
      
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'level' => 'required|string',
            'finish_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $language = Language::create(array_merge(
            $validator->validated(),
            ['user_id' => auth()->user()->id]
        ));

        $language = Language::find($language->id);

        return response()->json(($language), 200);

    }

    public function updateLanguage(Request $request) 
    {
      
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:languages,id',
            'name' => 'required|string|between:2,100',
            'level' => 'required|string',
            'finish_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $language = Language::find($validated_data['id']);

        if(!$language){
            return response()->json(['message' => 'Language not updated'], 422);
        }

        if($language->user_id != auth()->user()->id){
          return response()->json(['message' => 'Unauthorized'],401);
        }

        $language->update([
            'name'=> $validated_data['name'],
            'level' => $validated_data['level'],
            'finish_date'=> $validated_data['finish_date'],
        ]);
          
        return response()->json(($language), 200);

    }

    public function deleteLanguage($language_id)
    {

        $params = [
            'language_id' => $language_id
        ];

        $validator = Validator::make($params, [
            'language_id' => 'required|integer|exists:languages,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $language = Language::find($validated_data['language_id']);

        if($language->user_id != auth()->user()->id){
          return response()->json(['message' => 'Unauthorized'],401);
        }

        $language->delete();

        return response()->json(null, 204);

    }

}
