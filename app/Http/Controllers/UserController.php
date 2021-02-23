<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\Models\User;
use App\models\DrivingLicence;
use App\Models\UserType;
use App\Models\Education;
use App\Models\Language;
use App\Notifications\CustomNewUserNotification;

use Illuminate\Support\Facades\Validator;

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
    $user->user_type = UserType::find($user->user_type_id); //only(['rank', 'name']);
    unset($user->user_type_id);

    /* courses */
    $user->courses->transform(function ($course) {
      return [
        'id' => $course->id,
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

    /* avatar */
    if ($user->avatar) $user->avatar = true; //base64_encode(Storage::get($user->avatar));
    else $user->avatar = false;

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
    $avatar_path = auth()->user()->avatar;
    $user = $this->customizeFields(auth()->user());
    if ($user->avatar) {
      $user->avatar = [
        'avatar' => base64_encode(Storage::get($avatar_path)),
        'extension' => pathinfo(storage_path() . $avatar_path, PATHINFO_EXTENSION)
      ];
    };
    return response()->json($user);
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

    if (
      $author_rank <= $requested_user_rank ||
      $author_rank == 1 ||
      $minimum_rank != $author_rank ||
      auth()->user()->id == $id
    ) {
      $avatar_path = $user->avatar;
      $user = $this->customizeFields($user);
      if ($user->avatar) {
        $user->avatar = [
          'avatar' => base64_encode(Storage::get($avatar_path)),
          'extension' => pathinfo(storage_path() . $avatar_path, PATHINFO_EXTENSION)
        ];
      };
      return response()->json($user);
    } else {
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

    if ($minimum_rank != $author_rank) {
      $users = User::whereHas('userType', function ($q) use ($author_rank) {
        $q->where('rank', '>=', $author_rank);
      })->get();

      /* customize user fields */
      $users->transform(function ($user) {
        return $this->customizeFields($user);
      });
      return response()->json($users);
    } else {
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
    } else {
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
      'name' => 'sometimes|required|string|between:2,100',
      'surname' => 'sometimes|required|string|between:2,100',
      'email' => 'sometimes|required|string|email|max:100',
      'user_type_id' => 'sometimes|required|integer|exists:user_types,id',
      'dni' => 'nullable|string',
      'birth_date' => 'nullable|date',
      'actual_position' => 'nullable|string',
      'address_street' => 'nullable|string',
      'address_number' => 'nullable|string',
      'address_city' => 'nullable|string',
      'address_cp' => 'nullable|string',
      'address_country' => 'nullable|string',
      'phone' => 'nullable|string',
      'deactivated' => 'nullable|boolean',
      'driving_licences' => 'nullable|string',
    ]);

    $user = User::find($id);
    $author_rank = auth()->user()->userType->rank;
    $updated_user_rank = UserType::find($user->user_type_id)->rank;


    /* update password, email_verified_at or avatar: always forbidden */
    if ($request->password || $request->email_verified_at || $request->avatar) {
      return response()->json(['message' => 'User not updated1'], 422);
    }

    /* update user_type_id: forbidden if author_rank >= updated user rank  */
    if ($request->user_type_id) {
      $updated_user_new_rank = UserType::find($request->user_type_id)->rank;
      if (($author_rank >= $updated_user_rank) && ($author_rank == 1 && $updated_user_rank == 1 && $updated_user_new_rank != 1)) {
        return response()->json(['message' => 'User not updated2'], 422);
      }
    };

    /* update deactivated: forbidden if updated user is admin and author_rank >= updated user */
    if ($request->deactivated && ($author_rank >= $updated_user_rank || $updated_user_rank == 1)) {
      return response()->json(['message' => 'User not updated3'], 422);
    };

    /* update other fields: forbidden if author_rank >= updated_user_rank or author_rank is not admin (admin updating his/her own fields) */
    if ($author_rank >= $updated_user_rank && $author_rank != 1) {
      return response()->json(['message' => 'User not updated4'], 422);
    }

    /*update email: check if uniqueness */
    if ($request->email != $user->email) {
      if (User::where('email', $request->email)->first()) return response()->json(['message' => 'User not updated5'], 422);
    }

    $original_email = $user->email;
    $user->fill($request->all());

    /* driving licences */
    if ($request->driving_licences) {
      $user->drivingLicences()->delete();
      $driving_licences = array_filter(explode(',', str_replace(' ', '', $request->driving_licences)));
      foreach ($driving_licences as $driving_licence) {
        $user->drivingLicences()->insert([
          'user_id' => $user->id,
          'type' => $driving_licence,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ]);
      }
    }

    /* update email: send verification email and also set email_verified_at to null */
    if ($request->email != $original_email) {
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
  public function create(Request $request)
  {
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
      'phone' => 'string|nullable',
      'driving_licences' => 'string|nullable',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors()->toJson(), 400);
    }

    $author_rank = UserType::find(auth()->user()->user_type_id)->rank;
    $new_user_rank = UserType::find($request->get('user_type_id'))->rank;

    if ($author_rank < $new_user_rank) {
      $user = User::create(array_merge(
        $validator->validated(),
        ['password' => bcrypt($request->password)]
      ));

      if ($request->driving_licences) {
        $driving_licences = explode(',', str_replace(' ', '', $request->driving_licences));
        foreach ($driving_licences as $driving_licence) {
          $user->drivingLicences()->insert([
            'user_id' => $user->id,
            'type' => $driving_licence,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
          ]);
        }
      }

      // $user->sendEmailVerificationNotification();
      $user->notify(new CustomNewUserNotification([
        'email' => $user->email,
        'password' => $request->password
      ]));
      return response()->json($this->customizeFields(User::find($user->id)));
    } else {
      return response()->json(['message' => 'New user not created'], 422);
    }
  }

  public function setUserAvatar(Request $request, $id)
  {

    $user = User::find($id);
    if (!$user) {
      return response()->json(['message' => 'Avatar not updated'], 422);
    }

    $author_rank = auth()->user()->userType->rank;
    if ($author_rank >= $user->userType->rank && $author_rank != 1) {
      return response()->json(['message' => 'Unauthorized'], 401);
    }

    $validator = Validator::make($request->all(), [
      'avatar' => 'required|image|mimes:jpg,jpeg,png|max:2000',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors()->toJson(), 400);
    }

    try {
      Storage::delete($user->avatar);
    } catch (FileNotFoundException $e) {
    }

    $image = Image::make($request->file('avatar'));
    $image->fit(300, 300, function ($constraint) {
      $constraint->upsize();
    });
    $path = 'avatars' . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . 'avatar.jpg';
    $image->save(storage_path('app' . DIRECTORY_SEPARATOR . $path));

    // $extension = $request->file('avatar')->extension() == 'jpeg'? 'jpg': $request->file('avatar')->extension() ;
    // $path = $request->file('avatar')->storeAs('avatars'.DIRECTORY_SEPARATOR.$id,'avatar.'.$extension);

    /* avoid windows/linux conflict */
    $path = str_replace('/', DIRECTORY_SEPARATOR, $path);



    $user->avatar = $path;
    $user->save();

    return response()->json([
      'avatar' => base64_encode(Storage::get($user->avatar)),
      'extension' => pathinfo(storage_path() . $user->avatar, PATHINFO_EXTENSION)
    ], 200);
  }

  public function getUserAvatar($id)
  {

    $user = User::find($id);
    if (!$user) {
      return response()->json(['message' => 'User not found'], 422);
    }

    $author_rank = auth()->user()->userType->rank;
    if ($author_rank > $user->userType->rank && $author_rank != 1) {
      return response()->json(['message' => 'Unauthorized'], 401);
    }

    try {
      $file = Storage::get($user->avatar);
    } catch (FileNotFoundException $e) {
      return response()->json(['message' => 'Avatar not found'], 422);
    }


    return response()->json([
      'avatar' => base64_encode($file),
      'extension' => pathinfo(storage_path() . $user->avatar, PATHINFO_EXTENSION)
    ], 200);
  }

  public function deleteUserAvatar($id)
  {

    $user = User::find($id);
    if (!$user) {
      return response()->json(['message' => 'User not found'], 422);
    }

    $author_rank = auth()->user()->userType->rank;
    if ($author_rank > $user->userType->rank && $author_rank != 1) {
      return response()->json(['message' => 'Unauthorized'], 401);
    }

    Storage::delete($user->avatar);
    $user->avatar = null;
    $user->save();

    return response()->json(null, 200);
  }
}
