<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'question_media_url',
        'answer',
        'answer_media_url',
        'level',
        'diff',
        'category_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
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
