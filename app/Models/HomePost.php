<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomePost extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'title',
      'body',
      'position'
  ];

    protected $hidden = [
      'updated_at'
    ];

    public function homeDocuments(){
      return $this->hasMany(HomeDocument::class);
    }

}
