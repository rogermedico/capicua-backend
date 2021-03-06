<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\CustomVerifyEmailNotification;

use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'email_verified_at',
        'password',
        'birth_date',
        'actual_position',
        'address_street',
        'address_number',
        'address_city',
        'address_cp',
        'address_country',
        'phone',
        'dni',
        'user_type_id',
        'deactivated',
        'avatar_path',
        'dni_path',
        'sex_offense_certificate_path',
        'cv_path',
        'bank_account',
        'social_security_number'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pivot',
        'avatar_path',
        'dni_path',
        'sex_offense_certificate_path',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'deactivated' => 'boolean',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
      return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }

    public function courses(){
        return $this->belongsToMany(Course::class)->withPivot('number','expedition_date','valid_until')->withTimestamps();
    }

    public function userType(){
        return $this->belongsTo(UserType::class);
    }

    public function drivingLicences(){
        return $this->hasMany(DrivingLicence::class);
    }

    public function educations(){
        return $this->hasMany(Education::class);
    }

    public function personalDocuments(){
        return $this->hasMany(PersonalDocument::class);
    }
}
