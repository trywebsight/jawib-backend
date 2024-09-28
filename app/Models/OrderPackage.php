<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPackage extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'price' => 'decimal:2',
    ];
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
