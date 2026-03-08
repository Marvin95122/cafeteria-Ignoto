<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    protected $fillable = [
        'user_id',
        'opening_amount',
        'expected_amount',
        'actual_amount',
        'notes',
        'status',
        'opened_at',
        'closed_at'
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // Relación: Un corte de caja lo hace un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación: Un corte de caja puede tener muchos gastos registrados en ese turno
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}