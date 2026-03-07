<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    protected $fillable = [
        'name',
        'price',
        'active'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('price', 'active')
            ->withTimestamps();
    }

    //Relación con Ingredientes (Materia Prima)
    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'extra_ingredient')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
}
