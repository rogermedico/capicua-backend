<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $hidden = [
        'pivot',
        'created_at',
        'updated_at'
    ];

    public function users(){
        return $this->belongsToMany('App\Model\User')->withPivot('number','expedition_date','valid_until')->withTimestamps();
    }

}
