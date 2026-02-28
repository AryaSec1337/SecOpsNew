<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'it.security@megainsurance.co.id'],
            [
                'name' => 'IT Security Admin',
                'password' => Hash::make('socmegainsurance'),
                'email_verified_at' => now(),
            ]
        );
    }
}
