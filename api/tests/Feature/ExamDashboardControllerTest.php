<?php

namespace Tests\Feature;

use App\Models\ClassGroup;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ExamDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.stores.redis' => ['driver' => 'array']]);
        Cache::purge('redis');
    }

    public function test_it_shows_exam_dashboard(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $exam = $this->createExam($classGroup);
        $this->createFinishedAttempt($exam, $classGroup, 'Student One', 1, 5);
        $this->createFinishedAttempt($exam, $classGroup, 'Student Two', 2, 10);

        $response = $this->getJson("/api/exams/{$exam->id}/dashboard?per_page=10");

        $response
            ->assertOk()
            ->assertJsonPath('exam.id', $exam->id)
            ->assertJsonPath('average_score', 7.5)
            ->assertJsonPath('top_score', '10.00')
            ->assertJsonPath('ranking.total', 2)
            ->assertJsonPath('ranking.data.0.student.name', 'Student Two');
    }

    public function test_it_filters_dashboard_by_class_group(): void
    {
        $classA = ClassGroup::create(['code' => 'CLASS-A']);
        $classB = ClassGroup::create(['code' => 'CLASS-B']);
        $exam = $this->createExam($classA);
        $exam->classGroups()->attach($classB);
        $this->createFinishedAttempt($exam, $classA, 'Student A', 1, 5);
        $this->createFinishedAttempt($exam, $classB, 'Student B', 2, 10);

        $response = $this->getJson("/api/exams/{$exam->id}/dashboard?class_group_id={$classA->id}&per_page=10");

        $response
            ->assertOk()
            ->assertJsonPath('filters.class_group_id', (string) $classA->id)
            ->assertJsonPath('average_score', 5)
            ->assertJsonPath('ranking.total', 1)
            ->assertJsonPath('ranking.data.0.student.name', 'Student A');
    }

    public function test_it_validates_dashboard_filters(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $exam = $this->createExam($classGroup);

        $response = $this->getJson("/api/exams/{$exam->id}/dashboard?per_page=0");

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_it_blocks_dashboard_filter_for_unlinked_class_group(): void
    {
        $linkedClassGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $unlinkedClassGroup = ClassGroup::create(['code' => 'CLASS-B']);
        $exam = $this->createExam($linkedClassGroup);

        $response = $this->getJson("/api/exams/{$exam->id}/dashboard?class_group_id={$unlinkedClassGroup->id}");

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['class_group_id']);
    }

    private function createExam(ClassGroup $classGroup): Exam
    {
        $exam = Exam::create([
            'name' => 'Math Exam',
            'questions_count' => 2,
            'value' => 10,
        ]);

        $exam->classGroups()->attach($classGroup);

        return $exam;
    }

    private function createFinishedAttempt(
        Exam $exam,
        ClassGroup $classGroup,
        string $studentName,
        int $correctAnswersCount,
        float $score,
    ): ExamAttempt {
        return ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->createStudent($classGroup, $studentName)->id,
            'started_at' => now()->subMinutes(10),
            'finished_at' => now(),
            'correct_answers_count' => $correctAnswersCount,
            'score' => $score,
        ]);
    }

    private function createStudent(ClassGroup $classGroup, string $name): Student
    {
        $user = User::factory()->create();

        return Student::create([
            'user_id' => $user->id,
            'name' => $name,
            'class_group_id' => $classGroup->id,
        ]);
    }
}
