<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Teacher User',
            'email' => 'teacher@gmail.com',
            'password' => Hash::make('teacher123'),
        ]);

        ClassGroup::firstOrCreate(['code' => 'CLASS-A']);
        ClassGroup::firstOrCreate(['code' => 'CLASS-B']);
    }
}
