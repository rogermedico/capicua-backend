<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\models\DrivingLicence;
use App\Models\UserType;
use App\Models\Language;
use App\Notifications\CustomNewUserNotification;
use Illuminate\Database\QueryException;
use Validator;

class LanguageController extends Controller
{

  public function createLanguage(Request $request) {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|between:2,100',
      'level' => 'required|string',
      'finish_date' => 'nullable|date',
    ]);

    if($validator->fails()){
        return response()->json($validator->errors()->toJson(), 400);
    }

    $user = auth()->user();
    // $user = User::find($request->get('user_id'));
    // $author_rank = auth()->user()->userType->rank;
    // if( $author_rank >= $user->userType->rank && $author_rank != 1) {
    //   return response()->json(['message' => 'Unauthorized'],401);
    // }

    try {
      $language = Language::create(array_merge($validator->validated(),
        ['user_id' => $user->id]
      ));
    } catch(QueryException $e){
      return response()->json(['message' => 'Language not created'], 422);
    }

    $language = Language::find($language->id);

    return response()->json(($language), 200);

  }

  public function updateLanguage(Request $request) {
    $validator = Validator::make($request->all(), [
      'id' => 'required|integer|exists:languages,id',
      'name' => 'required|string|between:2,100',
      'level' => 'required|string',
      'finish_date' => 'nullable|date',
    ]);

    if($validator->fails()){
        return response()->json($validator->errors()->toJson(), 400);
    }

    $language = Language::find($request->get('id'));
    // $user = User::find($language['user_id']);
    // $author_rank = auth()->user()->userType->rank;
    // if( $author_rank >= $user->userType->rank && $author_rank != 1) {
    //   return response()->json(['message' => 'Unauthorized'],401);
    // }
    $user = auth()->user();

    if($language->user_id != $user->id){
      return response()->json(['message' => 'Unauthorized'],401);
    }

    if($language) {
      try {
        $language->update([
          'name'=> $request->get('name'),
          'level' => $request->get('level'),
          'finish_date'=> $request->get('finish_date'),
        ]);
      } catch(QueryException $e){
        return response()->json(['message' => 'Language not updated'], 422);
      }
    }
    else {
      return response()->json(['message' => 'Language not updated'], 422);

    } 

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

    if($validator->fails()){
      return response()->json($validator->errors()->toJson(), 400);
    }

    $language = Language::find($language_id);
    // $user = User::find($language['user_id']);
    // $author_rank = auth()->user()->userType->rank;
    // if( $author_rank >= $user->userType->rank && $author_rank != 1) {
    //   return response()->json(['message' => 'Unauthorized'],401);
    // }
    $user = auth()->user();
    if($language->user_id != $user->id){
      return response()->json(['message' => 'Unauthorized'],401);
    }

    $language->delete();
    return response()->json(null, 204);
  }

}
