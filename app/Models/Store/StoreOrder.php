<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreOrder extends Model
{
    use HasFactory;

    protected $table = 'store_orders';

    protected $fillable = [
        'user_id',
        'shipping',
        'total',
        'tap_id',
        'status',
        'payment_status',
    ];

    protected $casts = [
        'shipping' => 'float',
        'total' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function items()
    {
        return $this->hasMany(StoreOrderItem::class, 'order_id');
    }
}
