<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'discount' => 'float',
        'total' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function packages()
    {
        return $this->hasMany(OrderPackage::class);
    }
    public function first_package()
    {
        try {
            return $this->packages[0]->package->title;
        } catch (\Throwable $th) {
            return '';
        }
    }
}
