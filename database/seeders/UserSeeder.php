<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user=User::create(['firstname' => 'Tareq', 'lastname' => 'Diab', 'email' => 'tardib777@gmail.com', 'password' => 'Ta@123123']);
        $user->assignRole('admin');
    }
}
