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

    protected $fillable = ['name', 'email', 'country_code', 'phone', 'avatar', 'user_type', 'password', 'game_credits'];

    protected $hidden = ['password', 'remember_token', 'created_at', 'updated_at'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function games()
    {
        return $this->hasMany(Game::class);
    }

}
