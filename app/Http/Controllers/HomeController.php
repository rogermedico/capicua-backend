<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\HomePost;
use App\Models\UserType;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{

    public function getAllHomePosts()
    {
        $home_posts = HomePost::orderBy('position')->get();

        $home_posts->transform(function ($home_post) {
            $home_post->documents = $home_post->HomeDocuments;
            unset($home_post->HomeDocuments);
            return $home_post;
        });

        return response()->json($home_posts);

    }

    public function getHomePost($id)
    {

        $params = [
            'id' => $id
        ];

        $validator = Validator::make($params, [
            'id' => 'required|integer|exists:home_posts,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $home_post = HomePost::find($validated_data['id']);

        $home_post->documents = $home_post->HomeDocuments;
        unset($home_post->HomeDocuments);

        return response()->json($home_post);
        
    }

    public function createHomePost(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'body' => 'required|string'
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

        $home_post = HomePost::create([
            'title' => $validated_data['title'],
            'body' => $validated_data['body'],
            'position' => HomePost::max('position') + 1
        ]);

        $home_post->documents = [];

        return response()->json($home_post);

    }

    public function updateHomePost(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:home_posts,id',
            'title' => 'required|string',
            'body' => 'required|string'
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

        $home_post = HomePost::find($validated_data['id']);
        $home_post->title = $validated_data['title'];
        $home_post->body = $validated_data['body'];
        $home_post->save();

        $home_post->documents = $home_post->HomeDocuments;
        unset($home_post->HomeDocuments);

        return response()->json($home_post);

    }

    public function changePositionHomePost($origin, $destination){
        
        $params = [
            'origin' => $origin,
            'destination' => $destination
        ];

        $validator = Validator::make($params, [
            'origin' => 'required|integer|exists:home_posts,id',
            'destination' => 'required|integer|exists:home_posts,id|different:origin',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        DB::transaction(function () use($validated_data) {
            $origin_post = HomePost::find($validated_data['destination']);
            $destination_post = HomePost::find($validated_data['origin']);
            $origin_position = $origin_post->position;
            $origin_post->position = 0;
            $origin_post->save();
            $origin_post->position = $destination_post->position;
            $destination_post->position = $origin_position;
            $destination_post->save();
            $origin_post->save();
        });

        return response()->json(['message' => 'Positions changed'], 200);

    }

    public function deleteHomePost($home_post_id){

        $params = [
            'home_post_id' => $home_post_id
        ];

        $validator = Validator::make($params, [
            'home_post_id' => 'required|integer|exists:home_posts,id',
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
        
        $dir = 'home' . DIRECTORY_SEPARATOR . $home_post_id;

        if(Storage::exists($dir)){
            Storage::deleteDirectory($dir);
        }

        HomePost::destroy($home_post_id);

        return response()->json(['message' => 'Home post deleted'], 200);

    }

}
