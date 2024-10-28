<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreCategory extends Model
{
    use HasFactory;

    protected $table = 'store_categories';

    protected $fillable = [
        'title',
        'description',
        'image',
    ];

    public function products()
    {
        return $this->hasMany(StoreProduct::class, 'category_id');
    }

}
