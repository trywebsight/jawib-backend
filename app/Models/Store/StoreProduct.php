<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StoreProduct extends Model
{
    use HasFactory;

    protected $table = 'store_products';

    protected $fillable = [
        'title',
        'description',
        'category_id',
        'image',
        'price',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'price' => 'float',
    ];


    public function getImageAttribute($value)
    {
        if ($value) {
            return Storage::disk('do')->url($value);
        }
    }

    public function category()
    {
        return $this->belongsTo(StoreCategory::class, 'category_id');
    }

}
