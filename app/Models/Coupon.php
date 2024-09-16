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
    protected $fillable = [
        'code',
        'max_uses_per_user',
        'max_users',
        'total_uses',
        'expires_at',
    ];

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

    public function canBeUsedBy(User $user): bool
    {
        // Check if coupon is active
        if (!$this->isActive()) {
            return false;
        }

        // Check max uses per user
        $usage = $this->users()->where('user_id', $user->id)->first();

        if ($usage && $usage->pivot->uses >= $this->max_uses_per_user) {
            return false;
        }

        return true;
    }

    public function applyCoupon(User $user): bool
    {
        if (!$this->canBeUsedBy($user)) {
            return false;
        }

        // Start a database transaction
        return DB::transaction(function () use ($user) {
            // Increment total uses
            $this->increment('total_uses');

            // Update pivot table
            $usage = $this->users()->where('user_id', $user->id)->first();

            if ($usage) {
                // Increment uses
                $this->users()->updateExistingPivot($user->id, [
                    'uses' => $usage->pivot->uses + 1,
                ]);
            } else {
                // Attach user with uses = 1
                $this->users()->attach($user->id, ['uses' => 1]);
            }

            return true;
        });
    }
}
