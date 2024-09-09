<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'games_count',
        'price',
        'image',
        'active',
        'content',
    ];

    protected $casts = [
        'price' => 'float',
    ];

    public function purchase(User $user)
    {
        $user->addCredits($this->games_count);

        Transaction::create([
            'user_id' => $user->id,
            'package_id' => $this->id,
            'credit_change' => $this->games_count,
            'type' => 'purchase',
        ]);
    }

}
