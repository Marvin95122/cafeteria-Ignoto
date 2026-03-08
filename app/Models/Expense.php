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
        'receipt_image'
    ];

    // Relación: El gasto lo registró un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación: El gasto pertenece a un turno/corte de caja
    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }
}