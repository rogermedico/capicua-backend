<?php

namespace App\Http\Controllers;
use App\Models\UserType;
use App\Models\Course;
use Validator;

class ConstantsController extends Controller
{

    public function userTypes()
    {
        $author_rank = auth()->user()->userType->rank;
        $user_types = UserType::where('rank','>=', $author_rank)->get();
        return response()->json($user_types);
    }

    public function courseTypes()
    {
        $course_types = Course::all();
        return response()->json($course_types);
    }

}