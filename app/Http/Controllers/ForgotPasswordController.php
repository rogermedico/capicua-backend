<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class ForgotPasswordController extends Controller
{

    public function sendForgotPasswordEmail(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();

        $user = User::where('email',$validated_data['email'])->first();

        if($user->deactivated){
            return response()->json(['message' => 'Reset password email not sent'],400);
        }

        $status = Password::sendResetLink(
            [
                'email' => $validated_data['email']
            ]
        );

        if($status === Password::RESET_LINK_SENT){
            return response()->json(['message' => 'Reset password email sent']);
        }
        else{
            return response()->json(['message' => 'Reset password email not sent'],400);
        }

    }

    public function passwordReset(Request $request)
    {

        $validator = Validator::make($request->all(), [
          'token' => 'required|string',
          'email' => 'required|string|email|max:100|exists:users,email',
          'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $validated_data = $validator->valid();
        
        $user = User::where('email',$validated_data['email'])->first();

        if($user->deactivated){
            return response()->json(['message' => 'Password not updated'],400);
        }

        $status = Password::reset(
            [
                'email' => $validated_data['email'],
                'password' => $validated_data['password'],
                'password_confirmation' => $validated_data['password_confirmation'],
                'token' => $validated_data['token'],
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password)
                ])->save();
            }
        );

        if($status === Password::PASSWORD_RESET){
            return response()->json(['message' => 'Password updated']);
        }
        else{
            return response()->json(['message' => 'Password not updated'],400);
        }
    }

}
