<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'image', 'is_temp', 'user_id'];
    protected $casts = [
        'is_temp' => 'boolean',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function games()
    {
        return $this->belongsToMany(Game::class, 'game_categories');
    }

    public function getImageAttribute($value) {
        return $value ? Storage::disk('do')->url($value) : null;

    }
    // Local Scopes
    public function scopeSystem($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopeUser($query)
    {
        return $query->whereNotNull('user_id');
    }
}
