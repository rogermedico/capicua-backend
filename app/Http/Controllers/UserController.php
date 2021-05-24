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
use App\Models\User;
use App\Models\DrivingLicence;
use App\Models\UserType;
use App\Models\Education;
use App\Models\Language;
use App\Notifications\CustomNewUserNotification;

use Illuminate\Support\Facades\Validator;

// use App\Mail\TestMail;

class UserController extends Controller
{

    /* customize fields that are in another DB tables */
    private function customizeFields($user)
    {

        /* user type */
        $user->user_type = UserType::find($user->user_type_id); 
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
        if ($user->avatar_path) $user->avatar_file = true; 
        else $user->avatar_file = false;

        /* dni */
        if ($user->dni_path) $user->dni_file = true; 
        else $user->dni_file = false;

        /* sex_offense_certificate */
        if ($user->sex_offense_certificate_path) $user->sex_offense_certificate_file = true;
        else $user->sex_offense_certificate_file = false;

        /* cv */
        if ($user->cv_path) $user->cv_file = true;
        else $user->cv_file = false;

        return $user;

    }

    public function user()
    {

      $avatar_path = auth()->user()->avatar_path;
      $user = $this->customizeFields(auth()->user());
      
      if ($user->avatar_file) {
          $user->avatar_file = [
              'avatar' => base64_encode(Storage::get($avatar_path)),
              'extension' => pathinfo(storage_path() . $avatar_path, PATHINFO_EXTENSION)
          ];
      };

      return response()->json($user);

    }

    public function getUserById($id)
    {

      $params = [
          'id' => $id
      ];

      $validator = Validator::make($params, [
          'id' => 'required|integer|exists:users,id',
      ]);

      if ($validator->fails()) {
          return response()->json(['error' => $validator->errors()->first()], 400);
      }

      $validated_data = $validator->valid();

      $minimum_rank = UserType::max('rank');
      $user = User::find($validated_data['id']);
      $author_rank = auth()->user()->userType->rank;
      $requested_user_rank = $user->userType->rank;

      if (
          /* author more rank than requested user rank */
          $author_rank <= $requested_user_rank &&
          (
              /* author is admin */
              $author_rank == 1 ||
              /* author is not from lowest rank */
              $minimum_rank != $author_rank ||
              /* author requesting his own data */
              auth()->user()->id == $validated_data['id']
          )
      ) {
          $avatar_path = $user->avatar_path;
          $user = $this->customizeFields($user);
          if ($user->avatar_file) {
              $user->avatar_file = [
                  'avatar' => base64_encode(Storage::get($avatar_path)),
                  'extension' => pathinfo(storage_path() . $avatar_path, PATHINFO_EXTENSION)
              ];
          };

          return response()->json($user);
      } 
      else {
          return response()->json(['message' => 'Unauthorized'], 401);
      }

    }

    public function users()
    {

        $minimum_rank = UserType::max('rank');
        $author_rank = auth()->user()->userType->rank;

        if ($minimum_rank == $author_rank) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $users = User::all()->load('userType')->sortBy('userType.rank')->values();

        $users->transform(function ($user) {
          return $this->customizeFields($user);
        });

        return response()->json($users);

    }

    public function checkPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
          'password' => 'required|min:8|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        if ((Hash::check($validated_data['password'], auth()->user()->password))) {
          return response()->json(['password_equality' => true]);
        } else {
          return response()->json(['password_equality' => false]);
        }
    }

    public function changePassword(Request $request)
    {

      $validator = Validator::make($request->all(), [
          'old_password' => 'required|min:8',
          'password' => 'required|min:8|confirmed|different:old_password',
      ]);

      if ($validator->fails()) {
          return response()->json(['error' => $validator->errors()->first()], 400);
      }

      $validated_data = $validator->valid();

      if (!(Hash::check($validated_data['old_password'], auth()->user()->password))) {
          return response()->json(['message' => 'Unauthorized'], 401);
      }

      auth()->user()->password = bcrypt($validated_data['password']);
      auth()->user()->save();

      return response()->json(['message' => 'Password updated']);

    }

    public function updateProfile(Request $request)
    {

      $validator = Validator::make($request->all(), [
          'name' => 'sometimes|required|string|between:2,100',
          'surname' => 'sometimes|required|string|between:2,100',
          'email' => 'sometimes|required|string|email|max:100',
          'dni' => 'nullable|string',
          'birth_date' => 'nullable|date',
          'address_street' => 'nullable|string',
          'address_number' => 'nullable|string',
          'address_city' => 'nullable|string',
          'address_cp' => 'nullable|string',
          'address_country' => 'nullable|string',
          'phone' => 'nullable|string',
          'driving_licences' => 'nullable|string',
          'social_security_number' => 'nullable|string',
          'bank_account' => 'nullable|string'
      ]);

      if ($validator->fails()) {
          return response()->json(['error' => $validator->errors()->first()], 400);
      }

      $validated_data = $validator->valid();

      $user = auth()->user();

      /* in case author wants to update email check for uniqueness */
      if ($validated_data['email'] && $validated_data['email'] != $user->email) {
          if (User::where('email', $validated_data['email'])->first()) {
              return response()->json(['message' => 'User not updated'], 422);
          }
      }

      $original_email = $user->email;
      $user->fill($validated_data);

      /* driving licences */
      if (!empty($validated_data['driving_licences'])) {
          $user->drivingLicences()->delete();
          $driving_licences = array_filter(explode(',', str_replace(' ', '', $validated_data['driving_licences'])));
          foreach ($driving_licences as $driving_licence) {
              $user->drivingLicences()->insert([
                  'user_id' => $user->id,
                  'type' => $driving_licence,
                  'created_at' => Carbon::now(),
                  'updated_at' => Carbon::now()
              ]);
          }
      }
      else {
          $user->drivingLicences()->delete();
      }

      /* update email: send verification email and also set email_verified_at to null */
      if ($validated_data['email'] && $validated_data['email'] != $original_email) {
          $user->sendEmailVerificationNotification();
          $user->email_verified_at = null;
      }

      $user->save();

      $avatar_path = $user->avatar_path;
      $user = $this->customizeFields($user);
      if ($user->avatar_file) {
          $user->avatar_file = [
              'avatar' => base64_encode(Storage::get($avatar_path)),
              'extension' => pathinfo(storage_path() . $avatar_path, PATHINFO_EXTENSION)
          ];
      };

      return response()->json($user);

    }

    public function editUser(Request $request)
    {

        $validator = Validator::make($request->all(), [
          'user_id' => 'required|integer|exists:users,id',
          'user_type_id' => 'sometimes|required|integer|exists:user_types,id',
          'actual_position' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $user = User::find($validated_data['user_id']);
        $author_user_rank = auth()->user()->userType->rank;
        $objective_user_rank = UserType::find($user->user_type_id)->rank;

        /* update user_type_id: forbidden if author_rank >= updated user rank and user admin updating his own rank  */
        if (array_key_exists('user_type_id',$validated_data)) {
            $objective_user_new_rank = UserType::find($validated_data['user_type_id'])->rank;
            if (($author_user_rank >= $objective_user_rank) && ($author_user_rank == 1 && $objective_user_rank == 1 && $objective_user_new_rank != 1)) {
                return response()->json(['message' => 'User not updated'], 422);
            }
        };

        $user->fill($validated_data);
        $user->save();

        return response()->json($this->customizeFields($user), 200);

    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
          'name' => 'required|string|between:2,100',
          'surname' => 'required|string|between:2,100',
          'email' => 'required|string|email|max:100|unique:users',
          'password' => 'required|string|min:8',
          'user_type_id' => 'required|integer|exists:user_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $author_rank = UserType::find(auth()->user()->user_type_id)->rank;
        $new_user_rank = UserType::find($validated_data['user_type_id'])->rank;

        if ($author_rank >= $new_user_rank) {
            return response()->json(['message' => 'New user not created'], 422);
        }
        
        $user = User::create(array_merge(
            $validated_data,
            ['password' => bcrypt($validated_data['password'])]
        ));

        $user->notify(new CustomNewUserNotification([
            'email' => $user->email,
            'password' => $request->password
        ]));

        return response()->json($this->customizeFields(User::find($user->id)));
        
    }

    public function setUserAvatar(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpg,jpeg,png|max:4000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $user = auth()->user();

        $image = Image::make($validated_data['avatar']);
        $image->fit(300, 300, function ($constraint) {
          $constraint->upsize();
        });
        $filename = 'avatar.jpg';
        $dir = 'users' . DIRECTORY_SEPARATOR . $user->id;
        $path = $dir . DIRECTORY_SEPARATOR . $filename;

        if(Storage::exists($dir)){
            if(Storage::exists($user->avatar_path)){
                Storage::delete($user->avatar_path);
            }
        }
        else {
            Storage::makeDirectory($dir);
        }

        $image->save(storage_path('app' . DIRECTORY_SEPARATOR . $path));

        /* avoid windows/linux conflict */
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);

        $user->avatar_path = $path;
        $user->save();

        return response()->json([
            'avatar' => base64_encode(Storage::get($user->avatar_path)),
            'extension' => pathinfo(storage_path() . $user->avatar_path, PATHINFO_EXTENSION)
        ], 200);
    }

    public function getUserAvatar($id)
    {

        $params = [
            'id' => $id
        ];

        $validator = Validator::make($params, [
            'id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $user = User::find($validated_data['id']);

        $author_rank = auth()->user()->userType->rank;
        if ($author_rank > $user->userType->rank && $author_rank == UserType::max('rank')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $file = Storage::get($user->avatar_path);
        } catch (FileNotFoundException $e) {
            return response()->json(['message' => 'Avatar not found'], 422);
        }

        return response()->json([
            'avatar' => base64_encode($file),
            'extension' => pathinfo(storage_path() . $user->avatar_path, PATHINFO_EXTENSION)
        ], 200);

    }

    public function deleteUserAvatar()
    {

        $user = auth()->user();

        Storage::delete($user->avatar_path);
        $user->avatar_path = null;
        $user->save();

        return response()->json(null, 200);

    }

    public function activate(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $user = User::find($validated_data['user_id']);
        $author_user_rank = auth()->user()->userType->rank;
        $objective_user_rank = UserType::find($user->user_type_id)->rank;
        
        /* update deactivated: forbidden if updated user is admin and author_rank >= updated user */
        if ($author_user_rank >= $objective_user_rank || $objective_user_rank == 1) {
          return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user->deactivated = false;
        $user->save();

        return response()->json(['message' => 'User activated'], 200);

    }

    public function deactivate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $user = User::find($validated_data['user_id']);
        $author_user_rank = auth()->user()->userType->rank;
        $objective_user_rank = UserType::find($user->user_type_id)->rank;
        
        /* update deactivated: forbidden if updated user is admin and author_rank >= updated user */
        if ($author_user_rank >= $objective_user_rank || $objective_user_rank == 1) {
          return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user->deactivated = true;
        $user->save();

        return response()->json(['message' => 'User deactivated'], 200);

    }

    public function delete($user_id){

        $params = [
            'user_id' => $user_id
        ];

        $validator = Validator::make($params, [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $user = User::find($validator->valid()['user_id']);
        $author_user_rank = auth()->user()->userType->rank;
        $objective_user_rank = UserType::find($user->user_type_id)->rank;
      
        /* delete forbidden if user is admin or author_rank >= deleted user */
        if ($author_user_rank >= $objective_user_rank || $objective_user_rank == 1) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
      
        $dir = 'users' . DIRECTORY_SEPARATOR . $user->id;
        if(Storage::exists($dir)){
            Storage::deleteDirectory($dir);
        }
        $user->delete();

        return response()->json(['message' => 'User deleted'], 200);

    }

    public function setUserDni(Request $request)
    {

      $validator = Validator::make($request->all(), [
        'dni' => 'required|mimetypes:application/pdf|max:10000',
      ]);

      if ($validator->fails()) {
          return response()->json(['error' => $validator->errors()->first()], 400);
      }

      $validated_data = $validator->valid();

      $user = auth()->user();

      $filename = 'dni.pdf';
      $dir = 'users' . DIRECTORY_SEPARATOR . $user->id;
      $path = $dir . DIRECTORY_SEPARATOR . $filename;

      /* avoid windows/linux conflict */
      $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
      $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);

      if(Storage::exists($dir)){
          if(Storage::exists($user->dni_path)){
              Storage::delete($user->dni_path);
          }
      }
      else {
          Storage::makeDirectory($dir);
      }

      $validated_data['dni']->storeAs($dir,$filename);

      $user->dni_path = $path;
      $user->save();

      return response()->json(['message' => 'Dni upload successfully'], 200);
    }

    public function getUserDni($id)
    {

        $params = [
            'id' => $id
        ];

        $validator = Validator::make($params, [
            'id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $user = User::find($validated_data['id']);

        $author_rank = auth()->user()->userType->rank;
        if ($author_rank > $user->userType->rank && $author_rank == UserType::max('rank')) {
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

        return response()->json(['message' => 'Dni deleted'], 200);

    }

    public function setUserOffenses(Request $request)
    {

        $validator = Validator::make($request->all(), [
          'offenses' => 'required|mimetypes:application/pdf|max:10000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $user = auth()->user();

        $filename = 'offenses.pdf';
        $dir = 'users' . DIRECTORY_SEPARATOR . $user->id;
        $path = $dir . DIRECTORY_SEPARATOR . $filename;

        /* avoid windows/linux conflict */
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);

        if(Storage::exists($dir)){
            if(Storage::exists($user->sex_offense_certificate_path)){
                Storage::delete($user->sex_offense_certificate_path);
            }
        }
        else {
            Storage::makeDirectory($dir);
        }

        $validated_data['offenses']->storeAs($dir,$filename);

        $user->sex_offense_certificate_path = $path;
        $user->save();

        return response()->json(['message' => 'Offenses uploaded successfully'], 200);
    }

    public function getUserOffenses($id)
    {

        $params = [
            'id' => $id
        ];

        $validator = Validator::make($params, [
            'id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $user = User::find($validated_data['id']);

        $author_rank = auth()->user()->userType->rank;
        if ($author_rank > $user->userType->rank && $author_rank == UserType::max('rank')) {
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

        return response()->json(['message' => 'Offenses deleted'], 200);

    }

    public function setUserCV(Request $request)
    {

        $validator = Validator::make($request->all(), [
          'cv' => 'required|mimetypes:application/pdf|max:10000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $user = auth()->user();

        $filename = 'cv.pdf';
        $dir = 'users' . DIRECTORY_SEPARATOR . $user->id;
        $path = $dir . DIRECTORY_SEPARATOR . $filename;

        /* avoid windows/linux conflict */
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);

        if(Storage::exists($dir)){
            if(Storage::exists($user->cv_path)){
                Storage::delete($user->cv_path);
            }
        }
        else {
            Storage::makeDirectory($dir);
        }

        $validated_data['cv']->storeAs($dir,$filename);

        $user->cv_path = $path;
        $user->save();

        return response()->json(['message' => 'CV uploaded successfully'], 200);

    }

    public function getUserCV($id)
    {

        $params = [
            'id' => $id
        ];

        $validator = Validator::make($params, [
            'id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $user = User::find($validated_data['id']);

        $author_rank = auth()->user()->userType->rank;
        if ($author_rank > $user->userType->rank && $author_rank == UserType::max('rank')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $file = Storage::get($user->cv_path);
        } catch (FileNotFoundException $e) {
            return response()->json(['message' => 'CV not found'], 422);
        }

        return response()->json([
            'cv' => base64_encode($file),
            'extension' => pathinfo(storage_path() . $user->cv_path, PATHINFO_EXTENSION)
        ], 200);

    }

    public function deleteUserCV()
    {

        $user = auth()->user();

        Storage::delete($user->cv_path);
        $user->cv_path = null;
        $user->save();

        return response()->json(['message' => 'CV deleted'], 200);

    }

}