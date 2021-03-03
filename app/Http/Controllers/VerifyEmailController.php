<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified; 
use Illuminate\Support\Facades\Validator;
use App\Models\User;


class VerifyEmailController extends Controller
{

  public function sendVerifyEmail(Request $request){
    if(!auth()->user()->email_verified_at){
      auth()->user()->sendEmailVerificationNotification();
      return response()->json(['message' => 'Verification email sent.']);
    }
    else {
      return response()->json(['message' => 'Verification email not sent.'],400);
    }

    
  }

  public function verifyEmail(Request $request){

    $params = [
      'id' => $request->route('id'),
      'hash' => $request->route('hash')
    ];

    $validator = Validator::make($params, [
      'id' => 'required|integer|exists:users,id',
      'hash' => 'required|string',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors()->toJson(), 400);
    }

    $user = User::find($params['id']);

    if($user->deactivated){
      return response()->json(['message' => 'Email not verified.'],400);
    }

    if (!hash_equals((string) $params['hash'], sha1($user->getEmailForVerification()))) {
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
