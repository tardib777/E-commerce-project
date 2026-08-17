<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create(['name' => 'All']);
        Category::create(['name' => 'Phones and its accessories']);
        Category::create(['name' => 'Computers and its accessories']);
        Category::create(['name' => 'Electronics']);
        Category::create(['name' => 'Clothes']);
        Category::create(['name' => 'Shoes']);
    }
}
