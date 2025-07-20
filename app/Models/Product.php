<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'available_quantity',
        'category_id',
        'image',
        
    ];
    public function category(){
        return $this->belongsTo(Category::class);
    }
    public function order(){
        return $this->belongsTo(Order::class);
    }
}
