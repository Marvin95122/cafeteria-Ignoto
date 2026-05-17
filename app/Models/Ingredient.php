<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'unit', 'current_quantity', 'active'];

    // Relación con productos
    public function products()
    {
        return $this->belongsToMany(Product::class)
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function extras()
    {
        return $this->belongsToMany(Extra::class, 'extra_ingredient')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function getFullQuantityAttribute()
    {
        $qty = $this->current_quantity;
        $unit = $this->unit;

        // Lógica para Peso (Gramos a Kilos)
        if ($unit == 'g') {
            if ($qty >= 1000) {
                return ($qty / 1000) . ' kg'; // Ej: 1200g -> 1.2 kg
            }
            return $qty . ' g';
        }

        // Lógica para Líquidos (Mililitros a Litros)
        if ($unit == 'ml') {
            if ($qty >= 1000) {
                return ($qty / 1000) . ' L'; // Ej: 1500ml -> 1.5 L
            }
            return $qty . ' ml';
        }

        // Si son piezas u otra cosa, se devuelve normal
        return $qty . ' ' . $unit;
    }
}