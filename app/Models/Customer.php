<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'points',
    ];

    // Relación: Un cliente puede tener muchas órdenes (recibos)
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}