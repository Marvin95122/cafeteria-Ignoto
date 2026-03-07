<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Ingredient;

class Product extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'price',
        'stock',
        'image',
        'active',
        'use_dynamic_stock',
    ];
    
    // Relación con Categoría
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relación con Extras (Muchos a Muchos)
    public function extras()
    {
        return $this->belongsToMany(Extra::class)
            ->withPivot('price', 'active')
            ->withTimestamps();
    }

    // Relación con Ingredientes (Materia Prima)
    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class)
            ->withPivot('quantity') // Guardamos cuánto usa de cada ingrediente
            ->withTimestamps();
    }

    /**
     * ACCESOR INTELIGENTE PARA EL STOCK
     * * Se usa así: $product->calculated_stock
     * Si 'use_dynamic_stock' es true, calcula basado en ingredientes.
     * Si es false, devuelve el stock manual de siempre.
     */
    public function getCalculatedStockAttribute()
    {
        // Si no es dinámico, devolver el valor de la columna stock de la BD
        if (!$this->use_dynamic_stock) {
            return $this->stock;
        }

        // Si es dinámico pero no tiene ingredientes, es 0
        if ($this->ingredients->isEmpty()) {
            return 0;
        }

        // Cálculo matemático
        $possibleQuantities = $this->ingredients->map(function ($ingredient) {
            $required = $ingredient->pivot->quantity;
            if ($required <= 0) return 0;
            return floor($ingredient->current_quantity / $required);
        });

        return $possibleQuantities->min();
    }
}