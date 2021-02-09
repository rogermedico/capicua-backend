<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\models\DriverLicence;
use App\Models\UserType;
use App\Models\Education;
use App\Models\Language;
use Validator;

class UserTypesController extends Controller
{

  // public function __contstruct()
  // {
  //   $this->middleware('auth:api');//->except(['index','show']);
  // }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function userTypes()
  {
    $author_rank = auth()->user()->userType->rank;
    $user_types = UserType::where('rank','>=', $author_rank)->get();
    return response()->json($user_types);
  }

}