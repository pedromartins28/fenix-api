<?php

namespace Tests\Feature;

use App\Models\ClassGroup;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\Student;
use App\Models\User;
use App\Services\ExamDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamExecutionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_available_exams_for_student(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $student = $this->createStudent($classGroup);
        $exam = $this->createExam($classGroup);

        $response = $this->getJson("/api/students/{$student->id}/exams");

        $response
            ->assertOk()
            ->assertJsonPath('0.id', $exam->id)
            ->assertJsonPath('0.name', 'Math Exam')
            ->assertJsonPath('0.score', null);
    }

    public function test_it_shows_available_exam_summary_without_questions(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $student = $this->createStudent($classGroup);
        $exam = $this->createExam($classGroup);

        $response = $this->getJson("/api/students/{$student->id}/exams/{$exam->id}");

        $response
            ->assertOk()
            ->assertJsonPath('id', $exam->id)
            ->assertJsonMissingPath('questions');
    }

    public function test_it_starts_attempt(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $student = $this->createStudent($classGroup);
        $exam = $this->createExam($classGroup);

        $response = $this->postJson("/api/students/{$student->id}/exams/{$exam->id}/attempt");

        $response
            ->assertOk()
            ->assertJsonPath('exam_id', $exam->id)
            ->assertJsonPath('student_id', $student->id)
            ->assertJsonCount(2, 'exam.questions');

        $this->assertDatabaseHas('exam_attempts', [
            'exam_id' => $exam->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_it_shows_existing_attempt(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $student = $this->createStudent($classGroup);
        $exam = $this->createExam($classGroup);
        $attempt = $this->createAttempt($student, $exam);

        $response = $this->getJson("/api/students/{$student->id}/exams/{$exam->id}/attempt");

        $response
            ->assertOk()
            ->assertJsonPath('id', $attempt->id)
            ->assertJsonCount(2, 'exam.questions');
    }

    public function test_it_blocks_showing_attempt_before_start(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $student = $this->createStudent($classGroup);
        $exam = $this->createExam($classGroup);

        $response = $this->getJson("/api/students/{$student->id}/exams/{$exam->id}/attempt");

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['exam_id']);
    }

    public function test_it_submits_answers(): void
    {
        $dashboardService = $this->createMock(ExamDashboardService::class);
        $dashboardService
            ->expects($this->once())
            ->method('forgetDashboardCache');
        $this->app->instance(ExamDashboardService::class, $dashboardService);

        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $student = $this->createStudent($classGroup);
        $exam = $this->createExam($classGroup);
        $attempt = $this->createAttempt($student, $exam);
        $questions = $exam->questions()->with('options')->get();

        $response = $this->postJson("/api/exam-attempts/{$attempt->id}/answers", [
            'answers' => $questions
                ->map(fn (ExamQuestion $question): array => [
                    'exam_question_id' => $question->id,
                    'exam_question_option_id' => $this->correctOption($question)->id,
                ])
                ->all(),
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('id', $attempt->id)
            ->assertJsonPath('correct_answers_count', 2)
            ->assertJsonPath('score', '10.00');

        $this->assertDatabaseCount('student_exam_answers', 2);
    }

    public function test_it_validates_submit_answers_payload(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $student = $this->createStudent($classGroup);
        $exam = $this->createExam($classGroup);
        $attempt = $this->createAttempt($student, $exam);

        $response = $this->postJson("/api/exam-attempts/{$attempt->id}/answers", [
            'answers' => [],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['answers']);
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

    private function createExam(ClassGroup $classGroup): Exam
    {
        $exam = Exam::create([
            'name' => 'Math Exam',
            'questions_count' => 2,
            'value' => 10,
        ]);

        $this->createQuestion($exam, 'Question 1?', 1);
        $this->createQuestion($exam, 'Question 2?', 2);
        $exam->classGroups()->attach($classGroup);

        return $exam;
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

    private function createAttempt(Student $student, Exam $exam): ExamAttempt
    {
        return ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => now(),
        ]);
    }

    private function correctOption(ExamQuestion $question): ExamQuestionOption
    {
        return $question->options->firstWhere('is_correct', true);
    }
}
