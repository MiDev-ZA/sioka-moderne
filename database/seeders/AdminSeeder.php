<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'xavinorandrianantenaina@gmail.com'],
            [
                'name' => 'Stenidlas Xavino',
                'password' => Hash::make('STENIDLaS/200005'),
                'role' => 'admin',
            ]
        );
    }
}