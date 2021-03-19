<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\UserType;
use App\Models\PersonalDocument;


class PersonalDocumentsController extends Controller
{

    public function getAllDocuments(){

    }

    public function getPersonalDocument($id) {

    }

    public function getPersonalDocuments($user_id){

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createPersonalDocument(Request $request)
    {

      $validator = Validator::make($request->all(), [
        'user_id' => 'required|integer|exists:users,id',
        'document' => 'required|mimetypes:application/pdf|max:10000',
      ]);
  
      if ($validator->fails()) {
        return response()->json($validator->errors()->toJson(), 400);
      }

      $objective_user = User::find($validator->valid()['user_id']);
      $author_rank = auth()->user()->userType->rank;
      $objective_user_rank = UserType::find($objective_user->user_type_id)->rank;

      /* if user is employee forbidden */
      if ($author_rank == UserType::max('rank')) {
        return response()->json(['message' => 'Unauthorized'], 401);
      }

      $document = $validator->valid()['document'];
      
      $file_name_extension = $document->getClientOriginalName();
      $file_name = pathinfo($file_name_extension, PATHINFO_FILENAME);
      $file_extension = $document->getClientOriginalExtension();
      $modified_file_name_extension = Carbon::now()->format('Y_m_d_H_i_s') . '_' . str_replace(' ', '_', $file_name_extension);
      $dir = 'users' . DIRECTORY_SEPARATOR . $objective_user->id . DIRECTORY_SEPARATOR . 'documents';
      $path = $dir . DIRECTORY_SEPARATOR . $modified_file_name_extension;
  
      /* avoid windows/linux conflict */
      $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
      $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
  
      // if(Storage::exists($dir)){
      //   try {
      //     Storage::delete($user->sex_offense_certificate_path);
      //   } catch (FileNotFoundException $e) {
      //   }
      // }
      // else {
      //   Storage::makeDirectory($dir);
      // }
  
      $document->storeAs($dir,$modified_file_name_extension);

      $created_document = PersonalDocument::create([
        'user_id' => $objective_user->id,
        'original_name' => $file_name_extension,
        'path' => $path
      ]);
  
      return response()->json([
        'id' => $created_document->id,
        'user_id' => $objective_user->id,
        'original_name' => $created_document->original_name,
        'created_at' => $created_document->created_at
      ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deletePersonalDocument($id)
    {

        // $user = auth()->user();
    
        // Storage::delete($user->sex_offense_certificate_path);
        // $user->sex_offense_certificate_path = null;
        // $user->save();
    
    }
}
