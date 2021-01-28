<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\models\DriverLicence;
use App\Models\UserType;
use Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
      $this->middleware('auth:api', ['except' => ['login']]);
    }

    /* customize fields that are in another DB tables */
    private function customizeFields($user){
      /* user type */
      $user->user_type = UserType::find($user->user_type_id)->only(['rank','name']);
      unset($user->user_type_id);

      /* courses */
      $coursesOriginal = $user->courses;
      unset($user->courses);
      if($coursesOriginal->isNotEmpty()){
        $courses = [];
        foreach($coursesOriginal as $course){
          $parsedCourse = ['name' => $course->name];
          if($course->pivot->number) $parsedCourse['number'] = $course->pivot->number;
          if($course->pivot->expedition_date) $parsedCourse['expedition_date'] = $course->pivot->expedition_date;
          if($course->pivot->valid_until) $parsedCourse['valid_until'] = $course->pivot->valid_until;
          array_push($courses, $parsedCourse);
        };
        $user->courses = $courses;
      }

      /* driver licences */
      $drivingLicences = DriverLicence::where('user_id',$user->id)->get();
      if($drivingLicences->isNotEmpty()){
        $drivingLicences = $drivingLicences->sortBy('type')->flatten();
        $user->driving_licences = array_map(function($drivingLicence){
            return array_filter( $drivingLicence, function ($val) {
              return !is_null($val);
            });
        },$drivingLicences->toArray());
      }

      return $user;
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
      $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'surname' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:8',
            'user_type_id' => 'required|integer|exists:user_types,id'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password)]
                ));

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile() {
        return response()->json($this->customizeFields(auth()->user()));
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        return response()->json([
            'accessToken' => $token,
            'tokenType' => 'bearer',
            'expiresIn' => auth()->factory()->getTTL() * 60,
            'user' => $this->customizeFields(auth()->user())
			//'username' => auth()->user()->email
        ]);
    }
}
