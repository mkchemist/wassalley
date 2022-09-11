<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'name', 
        'quantity'
    ];


    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
