<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [
        'ingredient_id',
        'user_id',
        'type',
        'quantity',
        'reason'
    ];

    // Relacion: El movimiento pertenece a un Ingrediente especifico
    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    // Relacion: El movimiento fue registrado por un Usuario especifico
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}