<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    use HasFactory;

    public $table = 'educations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'user_id',
      'name',
      'finish_date',
      'finished'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      'created_at',
      'updated_at'
    ];

      /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
      'finished' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
