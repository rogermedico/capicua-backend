<?php

namespace App\Http\Controllers;
// use Illuminate\Auth\Events\Verified; 
// use Illuminate\Auth\Events\PasswordReset; 
// use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Mail;
// use Illuminate\Support\Facades\Password;
// use Illuminate\Support\Str;
use App\Models\User;
// use App\models\DriverLicence;
// use App\Models\UserType;
// use App\Models\Education;
// use App\Models\Language;
// use App\Mail\VerifyEmail;
use Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
      // $this->middleware('auth:api', ['except' => ['login','verifyEmail']]);
    }

    // /* customize fields that are in another DB tables */
    // private function customizeFields($user){

    //   /* user type */
    //   $user->user_type = UserType::find($user->user_type_id)->only(['rank','name']);
    //   unset($user->user_type_id);

    //   /* courses */
    //   $coursesOriginal = $user->courses;
    //   unset($user->courses);
    //     $courses = [];
    //     foreach($coursesOriginal as $course){
    //       $parsedCourse = [
    //         'name' => $course->name,
    //         'number' => $course->pivot->number,
    //         'expedition_date' => $course->pivot->expedition_date,
    //         'valid_until' => $course->pivot->valid_until
    //       ];
    //       array_push($courses, $parsedCourse);
    //     };
    //     $user->courses = $courses;

    //   /* driver licences */
    //   $user->driving_licences = DriverLicence::where('user_id',$user->id)->get();

    //   /* educations */
    //   $user->educations = Education::where('user_id',$user->id)->get();

    //   /* languages */
    //   $user->languages = Language::where('user_id',$user->id)->get();
      
    //   return $user;
    // }

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

        if(User::where('email',$request->email)->value('deleted')){
          return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token);
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
    // public function userProfile() {
    //     return response()->json($this->customizeFields(auth()->user()));
    // }

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
            'username' => auth()->user()->email,
            'emailVerified' => auth()->user()->hasVerifiedEmail()
        ]);
    }

    // public function sendVerifyEmail(Request $request){
    //   auth()->user()->sendEmailVerificationNotification();
    //   return response()->json(['message' => 'Verification email sent.']);
    // }

    // public function verifyEmail(Request $request){
    //   $user = User::find($request->route('id'));

    //   if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
    //       throw new AuthorizationException;
    //   }

    //   if ($user->hasVerifiedEmail()) return response()->json(['message' => 'Email already verified.']);

    //   if ($user->markEmailAsVerified()){
    //     event(new Verified($user));
    //     return response()->json(['message' => 'Email successfully verified.']);
    //   }
    //   else{
    //     return response()->json(['message' => 'ERROR: Email not verified.']);
    //   }
    // }

  //   public function sendForgotPasswordEmail(Request $request){
  //     $request->validate(['email' => 'required|email']);

  //     $status = Password::sendResetLink(
  //         $request->only('email')
  //     );

  //     if($status === Password::RESET_LINK_SENT){
  //       return response()->json(['message' => 'Reset password email sent.']);
  //     }
  //     else{
  //       return response()->json(['message' => 'ERROR: Reset password email not sent.']);
  //     }
  //   }

  //   public function forgotPasswordForm($token){
  //     return view('ForgotPasswordForm',['token' => $token]);
  //   }

  //   public function passwordReset(Request $request){
  //     $asdf = $request->validate([
  //       'token' => 'required',
  //       'email' => 'required|email',
  //       'password' => 'required|min:8|confirmed',
  //     ]);

  //     var_dump($asdf);

  //   $status = Password::reset(
  //       $request->only('email', 'password', 'password_confirmation', 'token'),
  //       function ($user, $password) use ($request) {
  //           $user->forceFill([
  //               'password' =>bcrypt($password)
  //           ])->save();

  //           $user->setRememberToken(Str::random(60));

  //           event(new PasswordReset($user));
  //       }
  //   );

  //   if($status === Password::PASSWORD_RESET){
  //     return response()->json(['message' => 'Password updated.']);
  //   }
  //   else{
  //     return response()->json(['message' => 'ERROR: Password not updated.']);
  //   }
  // }

}
