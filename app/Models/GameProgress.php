<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameProgress extends Model
{
    protected $fillable = ['game_id', 'data'];

    protected $casts = [
        'data' => 'json', // Automatically casts the JSON data to an array
    ];
}
