<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'url'];

    public function getUrlAttribute($value)
    {
        return Storage::disk('do')->url($value);
    }

}
