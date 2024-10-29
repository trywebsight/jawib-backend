<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StoreCategory extends Model
{
    use HasFactory;

    protected $table = 'store_categories';

    protected $fillable = [
        'title',
        'description',
        'image',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];


    public function getImageAttribute($value)
    {
        if ($value) {
            return Storage::disk('do')->url($value);
        }
    }
    public function products()
    {
        return $this->hasMany(StoreProduct::class, 'category_id');
    }
}
