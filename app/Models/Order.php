<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 
        'customer_name', 
        'total', 
        'payment_method', 
        'status'
    ];

    // Relación: Una orden pertenece a un usuario (El cajero/empleado que la cobró)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación: Una orden tiene muchos items/detalles (Los productos que se llevaron)
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}