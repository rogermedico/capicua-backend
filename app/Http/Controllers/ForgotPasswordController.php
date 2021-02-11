<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset; 

class ForgotPasswordController extends Controller
{

  public function sendForgotPasswordEmail(Request $request){
    $request->validate(['email' => 'required|email']);

    if(User::where('email',$request->email)->value('deleted')){
      return response()->json(['message' => 'Reset password email not sent.'],400);
    }

    $status = Password::sendResetLink(
        $request->only('email')
    );

    if($status === Password::RESET_LINK_SENT){
      return response()->json(['message' => 'Reset password email sent.']);
    }
    else{
      return response()->json(['message' => 'Reset password email not sent.'],400);
    }
  }

  public function passwordReset(Request $request){
    $request->validate([
      'token' => 'required',
      'email' => 'required|email',
      'password' => 'required|min:8|confirmed',
    ]);

    if(User::where('email',$request->email)->value('deleted')){
      return response()->json(['message' => 'Password not updated.'],400);
    }

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) use ($request) {
            $user->forceFill([
                'password' =>bcrypt($password)
            ])->save();

            $user->setRememberToken(Str::random(60));

            event(new PasswordReset($user));
        }
    );

    if($status === Password::PASSWORD_RESET){
      return response()->json(['message' => 'Password updated.']);
    }
    else{
      return response()->json(['message' => 'Password not updated.'],400);
    }
  }
}
