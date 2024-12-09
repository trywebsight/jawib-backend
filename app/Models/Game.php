<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'teams' => 'array',
        // 'selected_categories' => 'array', // If 'categories' is also an array
    ];
    protected $hidden = [
        'played_times', // If 'categories' is also an array
        'selected_categories', // If 'categories' is also an array
    ];

    public function teams()
    {
        if (is_array($this->teams)) {
            return array_column($this->teams, 'name');
        }
        return [];
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'game_categories');
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'game_questions');
    }
}
