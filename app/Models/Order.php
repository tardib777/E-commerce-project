<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['user_id', 'product_ids', 'quantity', 'total_price', 'status'];

   public function products()
    {
        return $this->belongsToMany(Product::class,'order_product')->withPivot('quantity','price')->withTimestamps();
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // app/Models/Order.php

}
