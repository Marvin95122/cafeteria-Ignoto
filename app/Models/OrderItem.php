<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 
        'product_id', 
        'quantity', 
        'unit_price', 
        'subtotal', 
        'extras'
    ];

    // Convierte automaticamente el texto JSON de la base de datos a un Array de PHP
    protected $casts = [
        'extras' => 'array',
    ];

    // Relacion: Este detalle pertenece a una orden general
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Relacion: Este detalle pertenece a un producto especifico del catalogo
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}