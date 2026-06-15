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
        'cash_received',
        'cash_change',
        'points_earned',
        'points_used',
        'status',
        'cancellation_reason',
        'cancellation_action',
        'cancelled_by',
        'cancelled_at'
    ];

    protected $casts = [
        'cancelled_at' => 'datetime',
        'cash_received' => 'decimal:2',
        'cash_change' => 'decimal:2',
        'points_earned' => 'integer',
        'points_used' => 'integer',
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