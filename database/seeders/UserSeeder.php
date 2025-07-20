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
        $user=User::create(['firstname' => 'Admin', 'lastname' => '(major)', 'email' => 'admin@gmail.com', 'password' => 'Ad@123123']);
        $user->assignRole('admin');
    }
}
