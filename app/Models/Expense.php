<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'cash_register_id',
        'description',
        'category',
        'amount',
        'receipt_image',
        'status',
        'cancelled_by',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'cancelled_at' => 'datetime',
    ];

    // Usuario que registró el gasto
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Caja a la que pertenece el gasto
    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    // Usuario que canceló/anuló el gasto
    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}