<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products=[
            Product::create(['name' => 'IPhone 15 cover','description' => 'give the phone a beautiful view, protect your phone from breaking if it falls down','price' => 10,'available_quantity' => 10,'category_id' => 1,'image' => 'productImages/default.png']),
            Product::create(['name' => 'IPhone 15','description' => 'a new phone produced at 2024','price' => 500,'available_quantity' => 10,'category_id' => 1,'image' => 'productImages/default.png']),
            Product::create(['name' => 'HP Pavilion G6','description' => 'produced at:   CPU speed:   RAM size:   ','price' => 1000,'available_quantity' => 10,'category_id' => 1,'image' => 'productsImages/1754241362HP-Pavilion-G6-pink.jpg']),
            Product::create(['name' => 'LG washer machine','description' => 'can contain 10 KG inside itm in addition to its job is washing and cleaning clothes, it is also can dry your clothes','price' => 500,'available_quantity' => 10,'category_id' => 1,'image' => 'productImages/default.png'])
        ];
        $products[0]->categories()->attach([1,2]);
        $products[1]->categories()->attach([1,2]);
        $products[2]->categories()->attach([1,3]);
        $products[3]->categories()->attach([1,4]);

    }
}
