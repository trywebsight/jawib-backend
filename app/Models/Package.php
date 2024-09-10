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
}
