<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SuggestedQuestion extends Model
{
    // use HasFactory;
    protected $fillable = [
        'question',
        'category_id',
        'user_id',
        'answer',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($suggestedQuestion) {
            if (!empty($suggestedQuestion->images)) {
                foreach ($suggestedQuestion->images as $image) {
                    // Extract the path from the URL
                    $path = parse_url($image, PHP_URL_PATH);
                    $path = ltrim($path, '/'); // Remove leading slash
                    // Delete the file from the 'do' disk
                    Storage::disk('do')->delete($path);
                }
            }
        });
    }

}
