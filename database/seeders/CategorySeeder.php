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
        insertCategory('أجهزة الموبايل وإكسسواراتها','أجهزة اللابتوب وملحقاتها','كهربائيات','الألبسة','الأحذية','قرطاسية');
    }
}
