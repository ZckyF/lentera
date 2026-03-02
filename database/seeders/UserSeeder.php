<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['identifier' => 'ADMIN001'],
            [
                'nik' => '0000000000000001',
                'name' => 'Super Admin',
                'role' => 'admin',
                'status' => 'active',
                'password' => Hash::make('password')
            ],
        );

        User::factory()->create([
            'identifier' => '246661058',
            'nik' => '64720319999888',
            'name' => 'M Ramadhani',
            'role' => 'mahasiswa'
        ]);

        User::factory()->count(5)->state(['role' => 'mahasiswa'])->create();
        User::factory()->count(3)->state(['role' => 'dosen'])->create();
        User::factory()->count(2)->state(['role' => 'staff'])->create();
    }
}

