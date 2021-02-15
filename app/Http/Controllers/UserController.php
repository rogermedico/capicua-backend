<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\models\DrivingLicence;
use App\Models\UserType;
use App\Models\Education;
use App\Models\Language;
use App\Notifications\CustomNewUserNotification;
use Validator;

class UserController extends Controller
{

  // public function __contstruct()
  // {
  //   $this->middleware('auth:api');//->except(['index','show']);
  // }

  /* customize fields that are in another DB tables */
  private function customizeFields($user)
  {

    /* user type */
    $user->user_type = UserType::find($user->user_type_id);//only(['rank', 'name']);
    unset($user->user_type_id);

    /* courses */
    $user->courses->transform(function ($course) {
      return [
        'name' => $course->name,
        'number' => $course->pivot->number,
        'expedition_date' => $course->pivot->expedition_date,
        'valid_until' => $course->pivot->valid_until
      ];
    });

    /* driver licences */
    $user->driving_licences = DrivingLicence::where('user_id', $user->id)->get();

    /* educations */
    $user->educations = Education::where('user_id', $user->id)->get();

    /* languages */
    $user->languages = Language::where('user_id', $user->id)->get();

    return $user;
  }



  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function user()
  {
    return response()->json($this->customizeFields(auth()->user()));
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function getUserById($id)
  {

    $minimum_rank = UserType::max('rank');
    $user = User::find($id);
    $author_rank = auth()->user()->userType->rank;
    $requested_user_rank = $user->userType->rank;

    if($author_rank <= $requested_user_rank ||
      $author_rank == 1 || 
      $minimum_rank != $author_rank || 
      auth()->user()->id == $id){
      return response()->json($this->customizeFields($user));
    }
    else {
      return response()->json(['message' => 'Unauthorized'], 401);
    }
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  // public function users()
  // {
  //   /* get all users */
  //   $users = User::all();

  //   /* customize user fields */
  //   $users->transform(function ($user) {
  //     return $this->customizeFields($user);
  //   });

  //   return response()->json($users);
  // }

    /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function users()
  {
    /* get all users */
    $minimum_rank = UserType::max('rank');
    $author_rank = auth()->user()->userType->rank;

    if($minimum_rank != $author_rank){
      $users = User::whereHas('userType', function($q) use($author_rank){
        $q->where('rank','>=', $author_rank );
      })->get();
  
      /* customize user fields */
      $users->transform(function ($user) {
        return $this->customizeFields($user);
      });
      return response()->json($users);
    }
    else {
      return response()->json(['message' => 'Unauthorized'], 401);
    }

  }

/**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function checkPassword(Request $request)
  {

    $request->validate([
      'password' => 'required|min:8|string',
    ]);

    if ((Hash::check($request->get('password'), auth()->user()->password))) {
      return response()->json(['password_equality' => true]);
    }
    else{
      return response()->json(['password_equality' => false]);
    }

  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function changePassword(Request $request)
  {

    $request->validate([
      'old_password' => 'required|min:8',
      'password' => 'required|min:8|confirmed|different:old_password',
    ]);

    if (!(Hash::check($request->get('old_password'), auth()->user()->password))) {
      return response()->json(['message' => 'Unauthorized'], 401);
    }

    auth()->user()->password = bcrypt($request->get('password'));
    auth()->user()->save();

    return response()->json(['message' => 'Password updated.']);
  }



/**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {

    $request->validate([
      'name' => 'string|between:2,100',
      'surname' => 'string|between:2,100',
      'email' => 'string|email|max:100|unique:users',
      'user_type_id' => 'integer|exists:user_types,id',
      'dni' => 'string',
      'birth_date' => 'date',
      'actual_position' => 'string',
      'address_street' => 'string',
      'address_number' => 'string',
      'address_city' => 'string',
      'address_cp' => 'string',
      'address_country' => 'string',
      'phone' => 'string',
      'deactivated' => 'boolean',
    ]);

    $user = User::find($id);
    $author_rank = auth()->user()->userType->rank;
    $updated_user_rank = UserType::find($user->user_type_id)->rank;

    /* update password or email_verified_at: always forbidden */
    if($request->password || $request->email_verified_at) {
      return response()->json(['message' => 'User not updated'],422);
    }

    /* update user_type_id: forbidden if author_rank >= new rank  */
    if($request->user_type_id && $author_rank >= $request->user_type_id){
      return response()->json(['message' => 'User not updated'],422);
    };

    /* update deactivated: forbidden if updated user is admin and author_rank >= updated user */
    if($request->deactivated && ($author_rank >= $updated_user_rank || $updated_user_rank == 1)){
      return response()->json(['message' => 'User not updated'],422);
    };

    /* update other fields: forbidden if author_rank >= updated_user_rank or author_rank is not admin (admin updating his/her own fields) */
    if ($author_rank >= $updated_user_rank && $author_rank != 1) {
      return response()->json(['message' => 'User not updated'],422);
    }

    $user->fill($request->all());

    /* update email: send verification email and also set email_verified_at to null */
    if($request->email) {
      $user->sendEmailVerificationNotification();
      $user->email_verified_at = null;
    }

    $user->save();

    return response()->json($this->customizeFields($user));

  }

  /**
   * Register a User.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function create(Request $request) {
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|between:2,100',
        'surname' => 'required|string|between:2,100',
        'email' => 'required|string|email|max:100|unique:users',
        'password' => 'required|string|min:8',
        'user_type_id' => 'required|integer|exists:user_types,id',
        'dni' => 'string|nullable',
        'birth_date' => 'date|nullable',
        'actual_position' => 'string|nullable',
        'address_street' => 'string|nullable',
        'address_number' => 'string|nullable',
        'address_city' => 'string|nullable',
        'address_cp' => 'string|nullable',
        'address_country' => 'string|nullable',
        'phone' => 'string|nullable'
    ]);

    if($validator->fails()){
        return response()->json($validator->errors()->toJson(), 400);
    }

    $author_rank = UserType::find(auth()->user()->user_type_id)->rank;
    $new_user_rank = UserType::find($request->get('user_type_id'))->rank;

    if ($author_rank < $new_user_rank) {
      $user = User::create(array_merge(
        $validator->validated(),
        ['password' => bcrypt($request->password)]
      ));
      // $user->sendEmailVerificationNotification();
      $user->notify(new CustomNewUserNotification([
        'email' => $user->email,
        'password' => $request->password
        ]));
      return response()->json($this->customizeFields(User::find($user->id)));
    }
    else{
      return response()->json(['message' => 'New user not created'],422);
    }

}


  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  // public function destroy($id)
  // {
  //   if ($request->user()->id == 3) {
  //     return response()->json(['error' => 'You cant delete users.'], 403);
  //   }
  //   $user->delete();
  //   return response()->json(null, 204);
  // }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  // public function store(Request $request)
  // {
  //   $this->validate($request, [
  //     'name' => 'required',
  //     'surname' => 'required',
  //     'email' => 'required',
  //     'password' => 'required',
  //     'user_type_id' => 'required',
  //   ]);
  //   $user = new User;
  //   $user->name = $request->name;
  //   $user->surname = $request->surname;
  //   $user->email = $request->email;
  //   $user->password = Hash::make($request->password);
  //   $user->user_type_id = $request->user_type_id;
  //   $user->save();

  //   return new UserResource($user);
  // }




}
