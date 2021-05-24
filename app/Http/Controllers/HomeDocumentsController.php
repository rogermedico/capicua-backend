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

    public function getHomeDocument($id) 
    {

      $params = [
          'id' => $id
      ];
  
      $validator = Validator::make($params, [
          'id' => 'required|integer|exists:home_documents,id',
      ]);
  
      if ($validator->fails()) {
          return response()->json(['error' => $validator->errors()->first()], 400);
      }

      $validated_data = $validator->valid();

      $home_document = HomeDocument::find($validated_data['id']);

      try {
          $file = Storage::get($home_document->path);
      } catch (FileNotFoundException $e) {
          return response()->json(['message' => 'Document not found'], 422);
      }
  
      return response()->json([
          'id' => $home_document->id,
          'home_post_id' => $home_document->home_post_id,
          'original_name' => $home_document->original_name,
          'created_at' => $home_document->created_at,
          'document' => base64_encode($file),
          'extension' => pathinfo(storage_path() . $home_document->path, PATHINFO_EXTENSION),
      ], 200);

    }

    public function createHomeDocument(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'home_post_id' => 'required|integer|exists:home_posts,id',
            'document' => 'required|mimetypes:application/pdf|max:10000',
        ]);
  
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $author_rank = auth()->user()->userType->rank;

        /* if user is employee forbidden */
        if ($author_rank == UserType::max('rank')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
      
        $file_name_extension = $validated_data['document']->getClientOriginalName();
        $file_name = pathinfo($file_name_extension, PATHINFO_FILENAME);
        $file_extension = $validated_data['document']->getClientOriginalExtension();
        $modified_file_name_extension = Carbon::now()->format('Ymd_His') . '_' . str_replace(' ', '_', $file_name_extension);
        $dir = 'home' . DIRECTORY_SEPARATOR . $validated_data['home_post_id'];
        $path = $dir . DIRECTORY_SEPARATOR . $modified_file_name_extension;
  
        /* avoid windows/linux conflict */
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
  
        $validated_data['document']->storeAs($dir,$modified_file_name_extension);

        $created_document = HomeDocument::create([
            'home_post_id' => $validated_data['home_post_id'],
            'original_name' => $file_name,
            'path' => $path
        ]);
  
        return response()->json([
            'id' => $created_document->id,
            'home_post_id' => $validated_data['home_post_id'],
            'original_name' => $created_document->original_name,
            'created_at' => $created_document->created_at,
        ], 200);
    }

    public function deleteHomeDocument($id)
    {

        $params = [
          'id' => $id
        ];
    
        $validator = Validator::make($params, [
          'id' => 'required|integer|exists:home_documents,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $home_document = HomeDocument::find($id);

        $author_rank = auth()->user()->userType->rank;

        /* if user is employee forbidden */
        if ($author_rank == UserType::max('rank')) {
          return response()->json(['message' => 'Unauthorized'], 401);
        }

        Storage::delete($home_document->path);

        $home_document->delete();

        return response()->json(['message' => 'Home document deleted'], 200);
    
    }

}
