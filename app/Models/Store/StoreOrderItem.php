<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreOrderItem extends Model
{
    use HasFactory;

    protected $table = 'store_order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
    ];

    public function order()
    {
        return $this->belongsTo(StoreOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(StoreProduct::class);
    }
}
