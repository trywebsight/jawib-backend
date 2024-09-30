<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameCategory extends Model
{
    protected $table = 'game_categories';

    protected $fillable = ['game_id', 'category_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
