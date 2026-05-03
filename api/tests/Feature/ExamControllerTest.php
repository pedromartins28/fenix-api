<?php

namespace Tests\Feature;

use App\Models\ClassGroup;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_exams_with_class_groups(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $exam = $this->createExam('Math Exam');
        $exam->classGroups()->attach($classGroup);

        $response = $this->getJson('/api/exams');

        $response
            ->assertOk()
            ->assertJsonPath('0.id', $exam->id)
            ->assertJsonPath('0.name', 'Math Exam')
            ->assertJsonPath('0.class_groups.0.id', $classGroup->id)
            ->assertJsonPath('0.class_groups.0.code', 'CLASS-A');
    }

    public function test_it_creates_exam_with_questions_and_options(): void
    {
        $response = $this->postJson('/api/exams', $this->validPayload());

        $response
            ->assertOk()
            ->assertJsonPath('name', 'Math Exam')
            ->assertJsonCount(2, 'questions')
            ->assertJsonCount(2, 'questions.0.options');

        $this->assertDatabaseHas('exams', ['name' => 'Math Exam']);
        $this->assertDatabaseCount('exam_questions', 2);
        $this->assertDatabaseCount('exam_question_options', 4);
    }

    public function test_it_shows_exam_with_questions_options_and_class_groups(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $exam = $this->createExam('Math Exam');
        $exam->classGroups()->attach($classGroup);
        $this->createQuestion($exam, 'Question 1?', 1);

        $response = $this->getJson("/api/exams/{$exam->id}");

        $response
            ->assertOk()
            ->assertJsonPath('id', $exam->id)
            ->assertJsonPath('class_groups.0.code', 'CLASS-A')
            ->assertJsonPath('questions.0.statement', 'Question 1?')
            ->assertJsonPath('questions.0.options.0.description', 'Correct option');
    }

    public function test_it_updates_exam(): void
    {
        $exam = $this->createExam('Old Exam');
        $this->createQuestion($exam, 'Old question?', 1);

        $response = $this->putJson("/api/exams/{$exam->id}", $this->validPayload([
            'name' => 'Updated Exam',
            'questions' => [
                $this->questionPayload('Updated question?', 1),
                $this->questionPayload('Another updated question?', 2),
            ],
        ]));

        $response
            ->assertOk()
            ->assertJsonPath('name', 'Updated Exam')
            ->assertJsonPath('questions.0.statement', 'Updated question?');

        $this->assertDatabaseHas('exams', ['id' => $exam->id, 'name' => 'Updated Exam']);
        $this->assertDatabaseMissing('exam_questions', ['exam_id' => $exam->id, 'statement' => 'Old question?']);
    }

    public function test_it_validates_exam_payload(): void
    {
        $payload = $this->validPayload();
        $payload['questions'][0]['statement'] = '';

        $response = $this->postJson('/api/exams', $payload);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['questions.0.statement']);
    }

    public function test_it_deletes_exam_without_attempts(): void
    {
        $exam = $this->createExam('Draft Exam');

        $response = $this->deleteJson("/api/exams/{$exam->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('exams', ['id' => $exam->id]);
    }

    public function test_it_blocks_deleting_exam_with_attempts(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $exam = $this->createExam('Published Exam');
        $student = $this->createStudent($classGroup);
        ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => now(),
        ]);

        $response = $this->deleteJson("/api/exams/{$exam->id}");

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['exam']);
        $this->assertDatabaseHas('exams', ['id' => $exam->id]);
    }

    private function createExam(string $name): Exam
    {
        return Exam::create([
            'name' => $name,
            'questions_count' => 2,
            'value' => 10,
        ]);
    }

    private function createQuestion(Exam $exam, string $statement, int $position): void
    {
        $question = $exam->questions()->create([
            'statement' => $statement,
            'position' => $position,
        ]);

        $question->options()->createMany([
            ['description' => 'Correct option', 'is_correct' => true],
            ['description' => 'Wrong option', 'is_correct' => false],
        ]);
    }

    private function createStudent(ClassGroup $classGroup): Student
    {
        $user = User::factory()->create();

        return Student::create([
            'user_id' => $user->id,
            'name' => 'Student User',
            'class_group_id' => $classGroup->id,
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_replace_recursive([
            'name' => 'Math Exam',
            'questions_count' => 2,
            'value' => 10,
            'questions' => [
                $this->questionPayload('Question 1?', 1),
                $this->questionPayload('Question 2?', 2),
            ],
        ], $overrides);
    }

    private function questionPayload(string $statement, int $position): array
    {
        return [
            'statement' => $statement,
            'position' => $position,
            'options' => [
                ['description' => 'Correct option', 'is_correct' => true],
                ['description' => 'Wrong option', 'is_correct' => false],
            ],
        ];
    }
}
