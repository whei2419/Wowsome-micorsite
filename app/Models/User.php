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
        'created_at',
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

    /**
     * Check if user has at least 1 successful referral (Tier 1)
     * A successful referral = invited user claimed either station 1 OR 2
     */
    public function hasCompletedReferrals($minCount = 1)
    {
        $completedCount = $this->referrals()
            ->whereHas('stationUser', function($query) {
                $query->whereIn('station_id', [1, 2]);
            })
            ->count();

        return $completedCount >= $minCount;
    }

    /**
     * Get count of successful referrals
     * Each referral who claimed station 1 OR 2 counts as 1 successful referral
     */
    public function getCompletedReferralsCount()
    {
        return $this->referrals()
            ->whereHas('stationUser', function($query) {
                $query->whereIn('station_id', [1, 2]);
            })
            ->count();
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

     /**
     * Check if the user is a protected admin user
     */
    public function isProtectedAdmin()
    {
        $protectedEmails = ['admin@gmail.com', 'superadmin@gmail.com', 'manager@gmail.com', 'support@gmail.com'];

        return in_array($this->email, $protectedEmails) || $this->hasRole('admin');
    }
}
