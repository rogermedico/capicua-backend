<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified; 
use Illuminate\Support\Facades\Validator;
use App\Models\User;


class VerifyEmailController extends Controller
{

    public function sendVerifyEmail(/*Request $request*/){
      
      if(!auth()->user()->email_verified_at){
          auth()->user()->sendEmailVerificationNotification();
          return response()->json(['message' => 'Verification email sent']);
      }
      else {
          return response()->json(['message' => 'Verification email not sent'],400);
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
          return response()->json(['error' => $validator->errors()->first()], 400);
      }

      $validated_data = $validator->valid();

      $user = User::find($validated_data['id']);

      if($user->deactivated){
          return response()->json(['message' => 'Email not verified'],400);
      }

      if (!hash_equals((string) $validated_data['hash'], sha1($user->getEmailForVerification()))) {
          return response()->json(['message' => 'Email not verified'],400);
      }

      if ($user->hasVerifiedEmail()) return response()->json(['message' => 'Email already verified'],400);

      if ($user->markEmailAsVerified()){
        return response()->json(['message' => 'Email successfully verified']);
      }
      else{
        return response()->json(['message' => 'Email not verified'],400);
      }

  }

}
