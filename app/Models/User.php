<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens;

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

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function addCredits($amount)
    {
        $this->increment('game_credits', $amount);
    }

    public function useCredit()
    {
        if ($this->game_credits > 0) {
            $this->decrement('game_credits');
            return true;
        }
        return false;
    }
}
