<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Bavix\Wallet\Traits\CanPay;
use Bavix\Wallet\Interfaces\Customer;

class User extends Authenticatable implements Wallet, Customer
{
    use HasFactory, HasApiTokens, HasWallet, CanPay;

    // protected $fillable = ['name', 'email', 'country_code', 'phone', 'avatar', 'user_type', 'password', 'game_credits'];
    protected $guarded = [];
    protected $hidden = ['password', 'remember_token', 'created_at', 'updated_at', 'email_verified_at', 'social_id', 'social_provider'];

    protected function casts(): array
    {
        return [
            'phone_verified' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function games()
    {
        return $this->hasMany(Game::class);
    }
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
    public function coupons()
    {
        return $this->belongsToMany(Coupon::class)->withPivot('uses')->withTimestamps();
    }


    public function getRankAttribute()
    {
        $points = $this->points;
        $ranks = [
            50 => 'master',
            40 => 'ninja',
            30 => 'senior',
            20 => 'mid-senior',
            10 => 'junior',
            0  => 'noobie',
        ];

        foreach ($ranks as $p => $rank) {
            if ($points >= $p) {
                return __($rank);
            }
        }
    }

    public function phone_number()
    {
        $country_code = ltrim($this->country_code, '+');
        if (substr($country_code, 0, 2) === '00') {
            $country_code = substr($country_code, 2);
        }

        $phone = $this->phone;
        if (substr($phone, 0, 1) === '0') {
            $phone = substr($phone, 1);
        }

        return $country_code . $phone;
    }
}
