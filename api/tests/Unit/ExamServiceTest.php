<?php

namespace Tests\Unit;

use App\Models\ClassGroup;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\User;
use App\Services\ExamService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ExamServiceTest extends TestCase
{
    use RefreshDatabase;

    private ExamService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ExamService::class);
    }

    public function test_it_creates_exam_with_questions_and_options(): void
    {
        $exam = $this->service->create($this->validPayload());

        $this->assertDatabaseHas('exams', [
            'id' => $exam->id,
            'name' => 'Math Exam',
            'questions_count' => 2,
            'value' => 10,
        ]);
        $this->assertDatabaseCount('exam_questions', 2);
        $this->assertDatabaseCount('exam_question_options', 4);
    }

    public function test_it_requires_questions_count_to_match_questions_amount(): void
    {
        $payload = $this->validPayload([
            'questions_count' => 3,
        ]);

        $this->expectException(ValidationException::class);

        $this->service->create($payload);
    }

    public function test_it_requires_exactly_one_correct_option_per_question(): void
    {
        $payload = $this->validPayload();
        $payload['questions'][0]['options'][1]['is_correct'] = true;

        $this->expectException(ValidationException::class);

        $this->service->create($payload);
    }

    public function test_it_updates_exam_and_replaces_questions_when_questions_change(): void
    {
        $exam = $this->service->create($this->validPayload());

        $updatedExam = $this->service->update($exam, $this->validPayload([
            'name' => 'Updated Exam',
            'questions' => [
                $this->questionPayload('Updated question?', 1),
                $this->questionPayload('Another updated question?', 2),
            ],
        ]));

        $this->assertSame('Updated Exam', $updatedExam->name);
        $this->assertDatabaseHas('exam_questions', [
            'exam_id' => $exam->id,
            'statement' => 'Updated question?',
        ]);
        $this->assertDatabaseMissing('exam_questions', [
            'exam_id' => $exam->id,
            'statement' => 'Question 1?',
        ]);
        $this->assertDatabaseCount('exam_questions', 2);
        $this->assertDatabaseCount('exam_question_options', 4);
    }

    public function test_it_blocks_update_when_exam_has_attempts(): void
    {
        $exam = $this->service->create($this->validPayload());
        $this->createAttemptForExam($exam);

        $this->expectException(ValidationException::class);

        $this->service->update($exam, $this->validPayload([
            'name' => 'Blocked update',
        ]));
    }

    public function test_it_deletes_exam_without_attempts_and_detaches_class_groups(): void
    {
        $exam = $this->service->create($this->validPayload());
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $exam->classGroups()->attach($classGroup);

        $this->service->delete($exam);

        $this->assertDatabaseMissing('exams', ['id' => $exam->id]);
        $this->assertDatabaseMissing('class_group_exam', [
            'exam_id' => $exam->id,
            'class_group_id' => $classGroup->id,
        ]);
    }

    public function test_it_blocks_delete_when_exam_has_attempts(): void
    {
        $exam = $this->service->create($this->validPayload());
        $this->createAttemptForExam($exam);

        $this->expectException(ValidationException::class);

        $this->service->delete($exam);
    }

    private function createAttemptForExam(Exam $exam): ExamAttempt
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $user = User::factory()->create();
        $student = Student::create([
            'user_id' => $user->id,
            'name' => 'Student User',
            'class_group_id' => $classGroup->id,
        ]);

        return ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => now(),
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
