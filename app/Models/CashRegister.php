<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    protected $fillable = [
        'user_id',
        'closed_by',
        'opening_amount',
        'expected_amount',
        'actual_amount',
        'difference_amount',
        'notes',
        'status',
        'opened_at',
        'closed_at'
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // Usuario que abrió la caja
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Usuario que cerró la caja
    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    // Gastos registrados en este turno/corte
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function adjustments()
    {
        return $this->hasMany(CashRegisterAdjustment::class);
    }
}