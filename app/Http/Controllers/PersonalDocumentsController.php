<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\UserType;
use App\Models\PersonalDocument;
use App\Notifications\NewPersonalDocumentNotification;

class PersonalDocumentsController extends Controller
{

    public function getAllDocumentsInfo(){

      $author_rank = auth()->user()->userType->rank;

      /* if user is employee forbidden */
      if ($author_rank == UserType::max('rank')) {
        return response()->json(['message' => 'Unauthorized'], 401);
      }

      $documents = PersonalDocument::orderBy('created_at','DESC')->get();

      return response()->json($documents);

    }

    public function getPersonalDocumentsInfo($user_id){

      $params = [
        'user_id' => $user_id
      ];

      $validator = Validator::make($params, [
        'user_id' => 'required|integer|exists:users,id',
      ]);
  
      if ($validator->fails()) {
        return response()->json($validator->errors()->toJson(), 400);
      }

      $author_rank = auth()->user()->userType->rank;

      /* if user is employee forbidden unless document owns him */
      if ($author_rank == UserType::max('rank') && $user_id != auth()->user()->id) {
        return response()->json(['message' => 'Unauthorized'], 401);
      }

      $documents = PersonalDocument::where('user_id', $user_id)->orderBy('created_at','DESC')->get();

      return response()->json($documents);

    }

    public function getPersonalDocument($id) {

      $params = [
        'id' => $id
      ];
  
      $validator = Validator::make($params, [
        'id' => 'required|integer|exists:personal_documents,id',
      ]);
  
      if($validator->fails()){
        return response()->json($validator->errors()->toJson(), 400);
      }

      $personal_document = PersonalDocument::find($id);

      $objective_user = User::find($personal_document->user_id);
      $author_rank = auth()->user()->userType->rank;
      $objective_user_rank = $objective_user->userType->rank;

      /* if user is employee forbidden unless document owns him */
      if ($author_rank == UserType::max('rank') && $personal_document->user_id != auth()->user()->id) {
        return response()->json(['message' => 'Unauthorized'], 401);
      }

      try {
        $file = Storage::get($personal_document->path);
      } catch (FileNotFoundException $e) {
        return response()->json(['message' => 'Document not found'], 422);
      }
  
      return response()->json([
        'id' => $personal_document->id,
        'user_id' => $personal_document->user_id,
        'name' => $personal_document->original_name,
        'date' => $personal_document->created_at,
        'document' => base64_encode($file),
        'extension' => pathinfo(storage_path() . $personal_document->path, PATHINFO_EXTENSION),
      ], 200);

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
      $objective_user_rank = $objective_user->userType->rank;

      /* if user is employee forbidden */
      if ($author_rank == UserType::max('rank')) {
        return response()->json(['message' => 'Unauthorized'], 401);
      }

      $document = $validator->valid()['document'];
      
      $file_name_extension = $document->getClientOriginalName();
      $file_name = pathinfo($file_name_extension, PATHINFO_FILENAME);
      $file_extension = $document->getClientOriginalExtension();
      $modified_file_name_extension = Carbon::now()->format('Ymd_His') . '_' . str_replace(' ', '_', $file_name_extension);
      $dir = 'users' . DIRECTORY_SEPARATOR . $objective_user->id . DIRECTORY_SEPARATOR . 'documents';
      $path = $dir . DIRECTORY_SEPARATOR . $modified_file_name_extension;
  
      /* avoid windows/linux conflict */
      $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
      $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
  
      $document->storeAs($dir,$modified_file_name_extension);

      $created_document = PersonalDocument::create([
        'user_id' => $objective_user->id,
        'original_name' => $file_name,//_extension,
        'path' => $path
      ]);

      $objective_user->notify(new NewPersonalDocumentNotification([
        'user_name' => $objective_user->name,
        'original_name' =>$created_document->original_name
      ]));
  
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

      $params = [
        'id' => $id
      ];
  
      $validator = Validator::make($params, [
        'id' => 'required|integer|exists:personal_documents,id',
      ]);
  
      if($validator->fails()){
        return response()->json($validator->errors()->toJson(), 400);
      }

      $personal_document = PersonalDocument::find($id);

      $objective_user = User::find($personal_document->user_id);
      $author_rank = auth()->user()->userType->rank;
      $objective_user_rank = $objective_user->userType->rank;

      /* delete personal document forbidden if author_rank >= objective user rank and user doing operation is not admin */
      if (($author_rank >= $objective_user_rank) && ($author_rank != 1)) {
        return response()->json(['message' => 'Unauthorized'], 401);
      };

      Storage::delete($personal_document->path);
      $personal_document->delete();
      return response()->json(null, 204);
    
    }
}
