<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegisterAdjustment extends Model
{
    protected $fillable = [
        'cash_register_id',
        'user_id',
        'field_name',
        'old_value',
        'new_value',
        'reason',
    ];

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}