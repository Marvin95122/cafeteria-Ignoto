<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 
        'customer_id',
        'customer_name', 
        'total', 
        'payment_method', 
        'status',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at'
    ];

    protected $casts = [
        'cancelled_at' => 'datetime',
    ];

    // Relación con el cajero
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con los detalles del ticket
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Vinculación con el Cliente VIP -->
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relación con el usuario que cancela
    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}