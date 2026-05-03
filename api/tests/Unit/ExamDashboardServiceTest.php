<?php

namespace Tests\Unit;

use App\Models\ClassGroup;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\User;
use App\Services\ExamDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ExamDashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    private ExamDashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.stores.redis' => ['driver' => 'array']]);
        Cache::purge('redis');

        $this->service = app(ExamDashboardService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_returns_average_top_score_and_ranking_for_finished_attempts(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $exam = $this->createExam([$classGroup]);
        $this->createFinishedAttempt($exam, $classGroup, 'First Student', 1, 5);
        $topAttempt = $this->createFinishedAttempt($exam, $classGroup, 'Top Student', 2, 10);
        $this->createStartedAttempt($exam, $classGroup, 'Unfinished Student');

        $dashboard = $this->service->getDashboard($exam, ['per_page' => 10]);

        $this->assertSame($exam->id, $dashboard['exam']['id']);
        $this->assertSame(7.5, $dashboard['average_score']);
        $this->assertSame('10.00', $dashboard['top_score']);
        $this->assertSame($topAttempt->id, $dashboard['top_attempt']['attempt_id']);
        $this->assertSame('Top Student', $dashboard['ranking']['data'][0]['student']['name']);
        $this->assertSame(2, $dashboard['ranking']['total']);
    }

    public function test_it_filters_dashboard_by_class_group(): void
    {
        $classA = ClassGroup::create(['code' => 'CLASS-A']);
        $classB = ClassGroup::create(['code' => 'CLASS-B']);
        $exam = $this->createExam([$classA, $classB]);
        $this->createFinishedAttempt($exam, $classA, 'Class A Student', 1, 5);
        $this->createFinishedAttempt($exam, $classB, 'Class B Student', 2, 10);

        $dashboard = $this->service->getDashboard($exam, [
            'class_group_id' => $classA->id,
            'per_page' => 10,
        ]);

        $this->assertSame(5.0, $dashboard['average_score']);
        $this->assertSame('5.00', $dashboard['top_score']);
        $this->assertSame($classA->id, $dashboard['filters']['class_group_id']);
        $this->assertSame(1, $dashboard['ranking']['total']);
        $this->assertSame('Class A Student', $dashboard['ranking']['data'][0]['student']['name']);
    }

    public function test_it_rejects_filter_for_class_group_not_linked_to_exam(): void
    {
        $linkedClassGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $unlinkedClassGroup = ClassGroup::create(['code' => 'CLASS-B']);
        $exam = $this->createExam([$linkedClassGroup]);

        $this->expectException(ValidationException::class);

        $this->service->getDashboard($exam, [
            'class_group_id' => $unlinkedClassGroup->id,
        ]);
    }

    public function test_it_returns_empty_metrics_when_exam_has_no_finished_attempts(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $exam = $this->createExam([$classGroup]);
        $this->createStartedAttempt($exam, $classGroup, 'Unfinished Student');

        $dashboard = $this->service->getDashboard($exam, ['per_page' => 10]);

        $this->assertNull($dashboard['average_score']);
        $this->assertNull($dashboard['top_score']);
        $this->assertNull($dashboard['top_attempt']);
        $this->assertSame(0, $dashboard['ranking']['total']);
        $this->assertSame([], $dashboard['ranking']['data']);
    }

    public function test_it_uses_cache_until_dashboard_cache_is_forgotten(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $exam = $this->createExam([$classGroup]);
        $this->createFinishedAttempt($exam, $classGroup, 'First Student', 1, 5);

        Carbon::setTestNow('2026-05-03 10:00:00');
        $firstDashboard = $this->service->getDashboard($exam, ['per_page' => 10]);

        Carbon::setTestNow('2026-05-03 10:04:00');
        $cachedDashboard = $this->service->getDashboard($exam, ['per_page' => 10]);

        $this->assertSame($firstDashboard['requested_at'], $cachedDashboard['requested_at']);

        $this->service->forgetDashboardCache($exam->id);
        Carbon::setTestNow('2026-05-03 10:05:00');
        $freshDashboard = $this->service->getDashboard($exam, ['per_page' => 10]);

        $this->assertNotSame($firstDashboard['requested_at'], $freshDashboard['requested_at']);
    }

    private function createExam(array $classGroups): Exam
    {
        $exam = Exam::create([
            'name' => 'Math Exam',
            'questions_count' => 2,
            'value' => 10,
        ]);

        $exam->classGroups()->attach(collect($classGroups)->pluck('id'));

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

    private function createStartedAttempt(Exam $exam, ClassGroup $classGroup, string $studentName): ExamAttempt
    {
        return ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->createStudent($classGroup, $studentName)->id,
            'started_at' => now(),
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
