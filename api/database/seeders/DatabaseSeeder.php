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
        $classB = ClassGroup::firstOrCreate(['code' => 'CLASS-B']);

        User::updateOrCreate(
            ['email' => 'teacher@gmail.com'],
            [
                'name' => 'Teacher User',
                'password' => Hash::make('teacher123'),
            ]
        );

        $students = [
            [
                'name' => 'Student User',
                'email' => 'student@gmail.com',
                'password' => 'student123',
                'class_group_id' => $classA->id,
            ],
            [
                'name' => 'Student Two',
                'email' => 'student2@gmail.com',
                'password' => 'student123',
                'class_group_id' => $classA->id,
            ],
            [
                'name' => 'Student Three',
                'email' => 'student3@gmail.com',
                'password' => 'student123',
                'class_group_id' => $classB->id,
            ],
        ];

        foreach ($students as $studentData) {
            $studentUser = User::updateOrCreate(
                ['email' => $studentData['email']],
                [
                    'name' => $studentData['name'],
                    'password' => Hash::make($studentData['password']),
                ]
            );

            Student::updateOrCreate(
                ['user_id' => $studentUser->id],
                [
                    'name' => $studentData['name'],
                    'class_group_id' => $studentData['class_group_id'],
                ]
            );
        }
    }
}
