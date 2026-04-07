<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable, HasRoles;

<<<<<<< Updated upstream
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
=======
  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    "heard",
    "follow",
    "appeal",
    "existing",
    "social_media",
    "existing",
    "email",
    "otp",
    "company",
    "otp_verified",
    "fname",
    "lname",
    "find",
    "dob",
    "password",
    "last_login_at",
    "race",
    "country",
    "baby_img",
    "baby_name",
    "charname",
    "marketing",
    "age",
    "gender",
  ];
  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = ["password", "remember_token"];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    "email_verified_at" => "datetime",
    "marketing" => "boolean",
  ];

  public function stationUser()
  {
    return $this->hasMany(StationUser::class);
  }

  /**
   * Check if the user is a protected admin user
   */
  public function isProtectedAdmin()
  {
    $protectedEmails = [
      "admin@gmail.com",
      "superadmin@gmail.com",
      "manager@gmail.com",
      "support@gmail.com",
    ];

    return in_array($this->email, $protectedEmails) || $this->hasRole("admin");
  }
>>>>>>> Stashed changes
}
