<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
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

        $studentOne = Student::whereHas('user', fn ($query) => $query->where('email', 'student@gmail.com'))->firstOrFail();
        $studentTwo = Student::whereHas('user', fn ($query) => $query->where('email', 'student2@gmail.com'))->firstOrFail();
        $studentThree = Student::whereHas('user', fn ($query) => $query->where('email', 'student3@gmail.com'))->firstOrFail();

        $examOne = $this->createExamWithQuestions(
            'Prova de Matemática',
            10,
            [
                [
                    'statement' => 'Quanto é 2 + 2?',
                    'options' => [
                        ['description' => '4', 'is_correct' => true],
                        ['description' => '5', 'is_correct' => false],
                    ],
                ],
                [
                    'statement' => 'Quanto é 3 x 3?',
                    'options' => [
                        ['description' => '9', 'is_correct' => true],
                        ['description' => '6', 'is_correct' => false],
                    ],
                ],
                [
                    'statement' => 'Qual número é par?',
                    'options' => [
                        ['description' => '8', 'is_correct' => true],
                        ['description' => '7', 'is_correct' => false],
                    ],
                ],
            ]
        );
        $examOne->classGroups()->sync([$classA->id]);

        $examTwo = $this->createExamWithQuestions(
            'Prova de Ciências',
            10,
            [
                [
                    'statement' => 'Qual planeta é conhecido como planeta vermelho?',
                    'options' => [
                        ['description' => 'Marte', 'is_correct' => true],
                        ['description' => 'Vênus', 'is_correct' => false],
                    ],
                ],
                [
                    'statement' => 'A água ferve a quantos graus Celsius ao nível do mar?',
                    'options' => [
                        ['description' => '100', 'is_correct' => true],
                        ['description' => '50', 'is_correct' => false],
                    ],
                ],
                [
                    'statement' => 'Qual gás é essencial para a respiração humana?',
                    'options' => [
                        ['description' => 'Oxigênio', 'is_correct' => true],
                        ['description' => 'Hélio', 'is_correct' => false],
                    ],
                ],
            ]
        );
        $examTwo->classGroups()->sync([$classA->id, $classB->id]);

        $this->createFinishedAttempt($examOne, $studentOne, [true, true, false]);
        $this->createFinishedAttempt($examOne, $studentTwo, [true, true, true]);
        $this->createFinishedAttempt($examTwo, $studentTwo, [true, false, true]);
        $this->createFinishedAttempt($examTwo, $studentThree, [true, true, false]);
    }

    private function createExamWithQuestions(string $name, float $value, array $questions): Exam
    {
        $exam = Exam::updateOrCreate(
            ['name' => $name],
            [
                'questions_count' => count($questions),
                'value' => $value,
            ]
        );

        $exam->questions()->each(function (ExamQuestion $question): void {
            $question->options()->delete();
        });
        $exam->questions()->delete();

        foreach ($questions as $position => $questionData) {
            $question = $exam->questions()->create([
                'statement' => $questionData['statement'],
                'position' => $position + 1,
            ]);

            foreach ($questionData['options'] as $optionData) {
                $question->options()->create($optionData);
            }
        }

        return $exam->load('questions.options');
    }

    private function createFinishedAttempt(Exam $exam, Student $student, array $answerPattern): void
    {
        $questions = $exam->questions()->with('options')->orderBy('position')->get();
        $correctAnswersCount = 0;

        $attempt = ExamAttempt::updateOrCreate(
            [
                'exam_id' => $exam->id,
                'student_id' => $student->id,
            ],
            [
                'started_at' => now()->subMinutes(20),
                'finished_at' => now()->subMinutes(5),
            ]
        );

        $attempt->answers()->delete();

        foreach ($questions as $index => $question) {
            $shouldBeCorrect = $answerPattern[$index] ?? false;
            $option = $this->optionForAnswer($question, $shouldBeCorrect);

            if ($option->is_correct) {
                $correctAnswersCount++;
            }

            $attempt->answers()->create([
                'exam_question_id' => $question->id,
                'exam_question_option_id' => $option->id,
            ]);
        }

        $attempt->update([
            'correct_answers_count' => $correctAnswersCount,
            'score' => round(($correctAnswersCount / $questions->count()) * (float) $exam->value, 2),
        ]);
    }

    private function optionForAnswer(ExamQuestion $question, bool $shouldBeCorrect): ExamQuestionOption
    {
        return $question->options->firstWhere('is_correct', $shouldBeCorrect);
    }
}
