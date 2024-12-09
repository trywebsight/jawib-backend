<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Store\StoreOrder;
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

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function store_orders()
    {
        return $this->hasMany(StoreOrder::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class)->withPivot('uses')->withTimestamps();
    }


    public function getRankAttribute()
    {
        $points = $this->points;
        $ranks = [
            60 => 'immportal',
            50 => 'diamond',
            40 => 'platinum',
            30 => 'gold',
            20 => 'silver',
            10 => 'bronze',
            0  => 'iron',
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
