<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\models\DriverLicence;
use App\Models\UserType;
use App\Models\Education;
use App\Models\Language;

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
    $user->user_type = UserType::find($user->user_type_id)->only(['rank', 'name']);
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
    $user->driving_licences = DriverLicence::where('user_id', $user->id)->get();

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
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function users()
  {
    /* get all users */
    $users = User::all();

    /* customize user fields */
    $users->transform(function ($user) {
      return $this->customizeFields($user);
    });

    return response()->json($users);
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
  // public function update(Request $request)
  // {

  //   if ($request->user()->user_type_id == 3) {

  //     return response()->json(['error' => 'You cant update users.'], 403);
  //   }



  //   $user->update($request->only(['name', 'surname', 'email']));


  //   return new UserResource($user);
  // }

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
