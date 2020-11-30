<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SummerCampTitle extends Model
{
    use HasFactory;

    protected $hidden = [
      'pivot',
      'created_at',
      'updated_at'
    ];

    public function users(){
        return $this->belongsToMany('App\Model\User')->withPivot('number')->withTimestamps();
    }

}
