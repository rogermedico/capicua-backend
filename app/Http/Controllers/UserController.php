<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserType;

class UserController extends Controller
{

    // public function __contstruct()
    // {
    //   $this->middleware('auth:api');//->except(['index','show']);
    // }

    /* customize fields that are in another DB tables */
    private function customizeFields($user){
      /* user type */
      $user->user_type = UserType::find($user->user_type_id)->only(['rank','name']);
      unset($user->user_type_id);

      /* summer camp titles */
      $summerCampTitles = [];
      $summerCampTitlesOriginal = $user->summerCampTitles;
      unset($user->summerCampTitles);
      foreach($summerCampTitlesOriginal as $summerCampTitle){
        array_push($summerCampTitles, [
          'name' => $summerCampTitle->name,
          'number' => $summerCampTitle->pivot->number

        ]);
      };
      $user->summer_camp_titles = $summerCampTitles;

      return $user;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /* get all users */
        $users = User::all();

        /* customize user fields */
        $users->transform(function($user){
          return $this->customizeFields($user);
        });

        return response()->json($users);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $this->validate($request, [
        'name' => 'required',
        'surname' => 'required',
        'email' => 'required',
        'password' => 'required',
        'user_type_id' => 'required',
      ]);
      $user = new User;
      $user->name = $request->name;
      $user->surname = $request->surname;
      $user->email = $request->email;
      $user->password = Hash::make($request->password);
      $user->user_type_id = $request->user_type_id;
      $user->save();

      return new UserResource($user);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return response()->json($this->customizeFields($user));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

      if ($request->user()->user_type_id == 3) {

        return response()->json(['error' => 'You cant update users.'], 403);

      }



      $user->update($request->only(['name','surname', 'email']));



      return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      if($request->user()->id == 3){
        return response()->json(['error' => 'You cant delete users.'], 403);
      }
        $user->delete();
        return response()->json(null,204);
    }
}
