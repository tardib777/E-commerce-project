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
    public function categories(){
        return $this->belongsToMany(Category::class,'category_product');
    }
    public function orderItem(){
        return $this->hasOne(OrderItem::class);
    }
}
