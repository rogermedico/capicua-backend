<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified; 
use App\Models\User;


class VerifyEmailController extends Controller
{

  public function sendVerifyEmail(Request $request){
    auth()->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verification email sent.']);
  }

  public function verifyEmail(Request $request){
    $user = User::find($request->route('id'));

    if($user->deleted){
      return response()->json(['message' => 'Email not verified.'],400);
    }

    if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
        throw new AuthorizationException;
    }

    if ($user->hasVerifiedEmail()) return response()->json(['message' => 'Email already verified.'],400);

    if ($user->markEmailAsVerified()){
      event(new Verified($user));
      return response()->json(['message' => 'Email successfully verified.']);
    }
    else{
      return response()->json(['message' => 'Email not verified.'],400);
    }
  }

}
