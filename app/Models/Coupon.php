<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;

class Coupon extends Model
{
    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('uses')
            ->withTimestamps();
    }

    public function isActive(): bool
    {
        // Check if the coupon has expired
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        // Check if the maximum number of users has been reached
        if ($this->max_users && $this->total_uses >= $this->max_users) {
            return false;
        }
        return true;
    }
}
