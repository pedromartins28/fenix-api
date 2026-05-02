<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\Student;
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

        $classA = ClassGroup::firstOrCreate(['code' => 'CLASS-A']);
        ClassGroup::firstOrCreate(['code' => 'CLASS-B']);

        User::updateOrCreate(
            ['email' => 'teacher@gmail.com'],
            [
                'name' => 'Teacher User',
                'password' => Hash::make('teacher123'),
            ]
        );

        $studentUser = User::updateOrCreate(
            ['email' => 'student@gmail.com'],
            [
                'name' => 'Student User',
                'password' => Hash::make('student123'),
            ]
        );

        Student::firstOrCreate(
            ['user_id' => $studentUser->id],
            [
                'name' => 'Student User',
                'class_group_id' => $classA->id,
            ]
        );
    }
}
