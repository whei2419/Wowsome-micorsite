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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'number',
        'country',
        'company',
        'marketing',
        'last_login_at',
        'referral_code',
        'referred_by',
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

     public function stationUser()
    {
        return $this->hasMany(StationUser::class);
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function hasCompletedReferrals()
    {
        return $this->referrals()
            ->whereHas('stationUser', function($query) {
                $query->whereIn('station_id', [1, 2]);
            })
            ->withCount(['stationUser' => function($query) {
                $query->whereIn('station_id', [1, 2]);
            }])
            ->get()
            ->filter(function($referral) {
                return $referral->station_user_count >= 2;
            })
            ->isNotEmpty();
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            if (empty($user->referral_code)) {
                // Generate 5-letter referral code
                $user->referral_code = self::generateReferralCode($user->id);
                $user->saveQuietly();
            }
        });
    }

    public static function generateReferralCode($userId)
    {
        // Generate a unique 5-letter code
        $maxAttempts = 100;
        $attempt = 0;

        do {
            // Create a random 5-letter code
            $code = '';
            $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

            // Use user ID and attempt number as seed for reproducibility
            $seed = $userId * 1000 + $attempt;
            mt_srand($seed);

            for ($i = 0; $i < 5; $i++) {
                $code .= $alphabet[mt_rand(0, 25)];
            }

            // Check if code already exists
            $exists = self::where('referral_code', $code)->exists();
            $attempt++;

        } while ($exists && $attempt < $maxAttempts);

        // If we couldn't find a unique code after max attempts, append user ID
        if ($exists) {
            $hash = md5('REF' . $userId);
            $letters = preg_replace('/[^a-zA-Z]/', '', $hash);
            $code = strtoupper(substr($letters, 0, 5));
        }

        return $code;
    }

    public static function getUserIdFromReferralCode($code)
    {
        // Find user by referral code
        $user = self::where('referral_code', $code)->first();
        return $user ? $user->id : null;
    }
}
