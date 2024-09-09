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
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
