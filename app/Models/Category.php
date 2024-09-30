<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'image', 'is_temp'];
    protected $casts = [
        'is_temp' => 'boolean',
    ];


    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function games()
    {
        return $this->belongsToMany(Game::class, 'game_categories');
    }
}
