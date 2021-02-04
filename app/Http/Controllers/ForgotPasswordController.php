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

    $status = Password::sendResetLink(
        $request->only('email')
    );

    if($status === Password::RESET_LINK_SENT){
      return response()->json(['message' => 'Reset password email sent.']);
    }
    else{
      return response()->json(['message' => 'ERROR: Reset password email not sent.']);
    }
  }

  public function passwordReset(Request $request){
    $request->validate([
      'token' => 'required',
      'email' => 'required|email',
      'password' => 'required|min:8|confirmed',
    ]);


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
      return response()->json(['message' => 'ERROR: Password not updated.']);
    }
  }
}
