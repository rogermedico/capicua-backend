<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\Models\HomePost;
use App\Models\HomeDocument;
use App\Models\UserType;

use Illuminate\Support\Facades\Validator;

use App\Mail\TestMail;

class HomeController extends Controller
{

  // // public function __contstruct()
  // // {
  // //   $this->middleware('auth:api');//->except(['index','show']);
  // // }

  // /* customize fields that are in another DB tables */
  // private function customizeFields($user)
  // {

  //   /* user type */
  //   $user->user_type = UserType::find($user->user_type_id); //only(['rank', 'name']);
  //   unset($user->user_type_id);

  //   /* courses */
  //   $user->courses->transform(function ($course) {
  //     return [
  //       'id' => $course->id,
  //       'name' => $course->name,
  //       'number' => $course->pivot->number,
  //       'expedition_date' => $course->pivot->expedition_date,
  //       'valid_until' => $course->pivot->valid_until
  //     ];
  //   });

  //   /* driver licences */
  //   $user->driving_licences = DrivingLicence::where('user_id', $user->id)->get();

  //   /* educations */
  //   $user->educations = Education::where('user_id', $user->id)->get();

  //   /* languages */
  //   $user->languages = Language::where('user_id', $user->id)->get();

  //   /* avatar */
  //   if ($user->avatar_path) $user->avatar_file = true; //base64_encode(Storage::get($user->avatar_file));
  //   else $user->avatar_file = false;

  //   /* dni */
  //   if ($user->dni_path) $user->dni_file = true; //base64_encode(Storage::get($user->avatar_file));
  //   else $user->dni_file = false;

  //   /* sex_offense_certificate */
  //   if ($user->sex_offense_certificate_path) $user->sex_offense_certificate_file = true; //base64_encode(Storage::get($user->avatar_file));
  //   else $user->sex_offense_certificate_file = false;

  //   return $user;
  // }



  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function getAllHomePosts()
  {
    $home_posts = HomePost::orderBy('position')->get();
    /* customize user fields */
    $home_posts->transform(function ($home_post) {
      $home_post->documents = $home_post->HomeDocuments;
      unset($home_post->HomeDocuments);
      return $home_post;
    });
    return response()->json($home_posts);
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function getHomePost($id)
  {

    $params = [
      'id' => $id
    ];

    $validator = Validator::make($params, [
      'id' => 'required|integer|exists:home_posts,id',
    ]);

    if($validator->fails()){
      return response()->json($validator->errors()->toJson(), 400);
    }

    $home_post = HomePost::find($id);
    /* customize user fields */
    // $home_posts->transform(function ($home_post) {
      $home_post->documents = $home_post->HomeDocuments;
      unset($home_post->HomeDocuments);
      // return $home_post;
    // });
    return response()->json($home_post);
  }

  public function createHomePost(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'title' => 'required|string',
      'body' => 'required|string'
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors()->toJson(), 400);
    }

    $author_rank = auth()->user()->userType->rank;

    /* if user is employee forbidden */
    if ($author_rank == UserType::max('rank')) {
      return response()->json(['message' => 'Unauthorized'], 401);
    }

    $title = $validator->valid()['title'];
    $body = $validator->valid()['body'];


    $home_post = HomePost::create([
      'title' => $title,
      'body' => $body
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
      return response()->json($validator->errors()->toJson(), 400);
    }

    $author_rank = auth()->user()->userType->rank;

    /* if user is employee forbidden */
    if ($author_rank == UserType::max('rank')) {
      return response()->json(['message' => 'Unauthorized'], 401);
    }

    $id = $validator->valid()['id'];
    $title = $validator->valid()['title'];
    $body = $validator->valid()['body'];


    $home_post = HomePost::find($id);
    $home_post->title = $title;
    $home_post->body = $body;
    $home_post->save();
    $home_post->documents = $home_post->HomeDocuments;
    unset($home_post->HomeDocuments);

    return response()->json($home_post);
  }

  public function deleteHomePost($home_post_id){

    $params = [
      'home_post_id' => $home_post_id
    ];

    $validator = Validator::make($params, [
      'home_post_id' => 'required|integer|exists:home_posts,id',
    ]);

    if($validator->fails()){
      return response()->json($validator->errors()->toJson(), 400);
    }

    $author_rank = auth()->user()->userType->rank;
    
    /* if user is employee forbidden */
    if ($author_rank == UserType::max('rank')) {
      return response()->json(['message' => 'Unauthorized'], 401);
    }
    else{
      $dir = 'home' . DIRECTORY_SEPARATOR . $home_post_id;
      if(Storage::exists($dir)){
          Storage::deleteDirectory($dir);
      }
      HomePost::destroy($home_post_id);

      return response()->json(['message' => 'Home post deleted'], 200);
    };

  }

  public function setUserDni(Request $request)
  {

    $user = auth()->user();

    $validator = Validator::make($request->all(), [
      'dni' => 'required|mimetypes:application/pdf|max:10000',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors()->toJson(), 400);
    }

    $dni = $validator->valid()['dni'];
    $filename = 'dni.pdf';
    $dir = 'users' . DIRECTORY_SEPARATOR . $user->id;
    $path = $dir . DIRECTORY_SEPARATOR . $filename;

    /* avoid windows/linux conflict */
    $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
    $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);

    if(Storage::exists($dir)){
      try {
        Storage::delete($user->dni_path);
      } catch (FileNotFoundException $e) {
      }
    }
    else {
      Storage::makeDirectory($dir);
    }

    $dni->storeAs($dir,$filename);

    $user->dni_path = $path;
    $user->save();

    return response()->json([
      'message' => 'Dni upload successfully'
      // 'avatar' => base64_encode(Storage::get($user->avatar_path)),
      // 'extension' => pathinfo(storage_path() . $user->avatar_path, PATHINFO_EXTENSION)
    ], 200);
  }

  public function getUserDni($id)
  {

    $user = User::find($id);
    if (!$user) {
      return response()->json(['message' => 'User not found'], 422);
    }

    $author_rank = auth()->user()->userType->rank;
    if ($author_rank > $user->userType->rank && $author_rank != 1 && $user->id != auth()->user()->id) {
      return response()->json(['message' => 'Unauthorized'], 401);
    }

    try {
      $file = Storage::get($user->dni_path);
    } catch (FileNotFoundException $e) {
      return response()->json(['message' => 'Dni not found'], 422);
    }


    return response()->json([
      'dni' => base64_encode($file),
      'extension' => pathinfo(storage_path() . $user->dni_path, PATHINFO_EXTENSION)
    ], 200);
  }

  public function deleteUserDni()
  {

    $user = auth()->user();

    Storage::delete($user->dni_path);
    $user->dni_path = null;
    $user->save();

  }

  public function setUserOffenses(Request $request)
  {

    $user = auth()->user();

    $validator = Validator::make($request->all(), [
      'offenses' => 'required|mimetypes:application/pdf|max:10000',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors()->toJson(), 400);
    }

    $offenses = $validator->valid()['offenses'];
    $filename = 'offenses.pdf';
    $dir = 'users' . DIRECTORY_SEPARATOR . $user->id;
    $path = $dir . DIRECTORY_SEPARATOR . $filename;

    /* avoid windows/linux conflict */
    $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
    $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);

    if(Storage::exists($dir)){
      try {
        Storage::delete($user->sex_offense_certificate_path);
      } catch (FileNotFoundException $e) {
      }
    }
    else {
      Storage::makeDirectory($dir);
    }

    $offenses->storeAs($dir,$filename);

    $user->sex_offense_certificate_path = $path;
    $user->save();

    return response()->json([
      'message' => 'Offenses uploaded successfully'
      // 'avatar' => base64_encode(Storage::get($user->avatar_path)),
      // 'extension' => pathinfo(storage_path() . $user->avatar_path, PATHINFO_EXTENSION)
    ], 200);
  }

  public function getUserOffenses($id)
  {

    $user = User::find($id);
    if (!$user) {
      return response()->json(['message' => 'User not found'], 422);
    }

    $author_rank = auth()->user()->userType->rank;
    if ($author_rank > $user->userType->rank && $author_rank != 1 && $user->id != auth()->user()->id) {
      return response()->json(['message' => 'Unauthorized'], 401);
    }

    try {
      $file = Storage::get($user->sex_offense_certificate_path);
    } catch (FileNotFoundException $e) {
      return response()->json(['message' => 'Offenses not found'], 422);
    }


    return response()->json([
      'offenses' => base64_encode($file),
      'extension' => pathinfo(storage_path() . $user->sex_offense_certificate_path, PATHINFO_EXTENSION)
    ], 200);
  }

  public function deleteUserOffenses()
  {

    $user = auth()->user();

    Storage::delete($user->sex_offense_certificate_path);
    $user->sex_offense_certificate_path = null;
    $user->save();

  }



}
