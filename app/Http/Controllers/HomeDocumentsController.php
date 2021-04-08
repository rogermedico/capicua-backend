<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use App\Models\HomePost;
use App\Models\UserType;
use App\Models\HomeDocument;


class HomeDocumentsController extends Controller
{

    public function getHomeDocument($id) {

      $params = [
        'id' => $id
      ];
  
      $validator = Validator::make($params, [
        'id' => 'required|integer|exists:home_documents,id',
      ]);
  
      if($validator->fails()){
        return response()->json($validator->errors()->toJson(), 400);
      }

      $home_document = HomeDocument::find($id);

      try {
        $file = Storage::get($home_document->path);
      } catch (FileNotFoundException $e) {
        return response()->json(['message' => 'Document not found'], 422);
      }
  
      return response()->json([
        'id' => $home_document->id,
        'home_post_id' => $home_document->home_post_id,
        'name' => $home_document->original_name,
        'document' => base64_encode($file),
        'extension' => pathinfo(storage_path() . $home_document->path, PATHINFO_EXTENSION),
      ], 200);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createHomeDocument(Request $request)
    {

      $validator = Validator::make($request->all(), [
        'home_post_id' => 'required|integer|exists:home_posts,id',
        'document' => 'required|mimetypes:application/pdf|max:10000',
      ]);
  
      if ($validator->fails()) {
        return response()->json($validator->errors()->toJson(), 400);
      }

      // $objective_user = User::find($validator->valid()['user_id']);
      $author_rank = auth()->user()->userType->rank;
      // $objective_user_rank = $objective_user->userType->rank;

      /* if user is employee forbidden */
      if ($author_rank == UserType::max('rank')) {
        return response()->json(['message' => 'Unauthorized'], 401);
      }

      $home_post_id = $validator->valid()['home_post_id'];
      $document = $validator->valid()['document'];
      
      $file_name_extension = $document->getClientOriginalName();
      $file_name = pathinfo($file_name_extension, PATHINFO_FILENAME);
      $file_extension = $document->getClientOriginalExtension();
      $modified_file_name_extension = Carbon::now()->format('Ymd_His') . '_' . str_replace(' ', '_', $file_name_extension);
      $dir = 'home' . DIRECTORY_SEPARATOR . $home_post_id;
      $path = $dir . DIRECTORY_SEPARATOR . $modified_file_name_extension;
  
      /* avoid windows/linux conflict */
      $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
      $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
  
      $document->storeAs($dir,$modified_file_name_extension);

      $created_document = HomeDocument::create([
        'home_post_id' => $home_post_id,
        'original_name' => $file_name,//_extension,
        'path' => $path
      ]);
  
      return response()->json([
        'id' => $created_document->id,
        'home_post_id' => $home_post_id,
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
    public function deleteHomeDocument($id)
    {

      $params = [
        'id' => $id
      ];
  
      $validator = Validator::make($params, [
        'id' => 'required|integer|exists:home_documents,id',
      ]);
  
      if($validator->fails()){
        return response()->json($validator->errors()->toJson(), 400);
      }

      $home_document = HomeDocument::find($id);

      // $objective_user = User::find($home_document->user_id);
      $author_rank = auth()->user()->userType->rank;
      // $objective_user_rank = $objective_user->userType->rank;

      /* if user is employee forbidden */
      if ($author_rank == UserType::max('rank')) {
        return response()->json(['message' => 'Unauthorized'], 401);
      }

      Storage::delete($home_document->path);
      $home_document->delete();
      return response()->json(null, 204);
    
    }
}
