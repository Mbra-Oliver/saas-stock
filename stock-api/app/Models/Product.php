<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class)
            ->withPivot('stock_quantity', 'location')
            ->withTimestamps();
    }
}
