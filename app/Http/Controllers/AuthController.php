<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;

class AuthController extends Controller
{
    
	  /* login */
    public function login(Request $request)
    {

      	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

		    $validated_data = $validator->valid();

        if(User::where('email', $validated_data['email'])->value('deactivated')){
          return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token);
    }

	  /* logout */
    public function logout() 
    {
        auth()->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }

	  /* refresh */
    public function refresh() 
    {
        return $this->createNewToken(auth()->refresh());
    }

	  /* build and return authentication object */
    protected function createNewToken($token)
    {
        return response()->json([
            'accessToken' => $token,
            'tokenType' => 'Bearer',
            'expiresIn' => auth()->factory()->getTTL() * 60,
            'username' => auth()->user()->email,
            'emailVerified' => auth()->user()->hasVerifiedEmail()
        ]);
    }

}
