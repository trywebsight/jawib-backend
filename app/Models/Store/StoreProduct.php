<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function category()
    {
        return $this->belongsTo(StoreCategory::class, 'category_id');
    }

}
