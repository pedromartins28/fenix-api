<?php

namespace Tests\Unit;

use App\Models\ClassGroup;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\Student;
use App\Models\User;
use App\Services\ExamDashboardService;
use App\Services\ExamExecutionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ExamExecutionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_only_exams_available_for_student_class_group(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $otherClassGroup = ClassGroup::create(['code' => 'CLASS-B']);
        $student = $this->createStudent($classGroup);
        $availableExam = $this->createExam($classGroup);
        $this->createExam($otherClassGroup);

        $exams = $this->service()->listAvailableExams($student);

        $this->assertCount(1, $exams);
        $this->assertSame($availableExam->id, $exams->first()['id']);
    }

    public function test_it_blocks_student_from_seeing_exam_from_another_class_group(): void
    {
        $student = $this->createStudent(ClassGroup::create(['code' => 'CLASS-A']));
        $exam = $this->createExam(ClassGroup::create(['code' => 'CLASS-B']));

        $this->expectException(ValidationException::class);

        $this->service()->showAvailableExam($student, $exam);
    }

    public function test_it_starts_attempt_and_returns_exam_questions(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $student = $this->createStudent($classGroup);
        $exam = $this->createExam($classGroup);

        $attempt = $this->service()->startAttempt($student, $exam);

        $this->assertDatabaseHas('exam_attempts', [
            'exam_id' => $exam->id,
            'student_id' => $student->id,
        ]);
        $this->assertSame($exam->id, $attempt['exam_id']);
        $this->assertNotNull($attempt['started_at']);
        $this->assertCount(2, $attempt['exam']['questions']);
        $this->assertArrayNotHasKey('is_correct', $attempt['exam']['questions'][0]['options'][0]);
    }

    public function test_it_blocks_starting_same_exam_twice(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $student = $this->createStudent($classGroup);
        $exam = $this->createExam($classGroup);
        $service = $this->service();

        $service->startAttempt($student, $exam);

        $this->expectException(ValidationException::class);

        $service->startAttempt($student, $exam);
    }

    public function test_it_requires_attempt_before_showing_questions(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $student = $this->createStudent($classGroup);
        $exam = $this->createExam($classGroup);

        $this->expectException(ValidationException::class);

        $this->service()->showAttempt($student, $exam);
    }

    public function test_it_submits_answers_and_calculates_score(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $student = $this->createStudent($classGroup);
        $exam = $this->createExam($classGroup);
        $attempt = $this->createAttempt($student, $exam);
        [$firstQuestion, $secondQuestion] = $exam->questions()->with('options')->get();

        $result = $this->service(cacheFlushes: 1)->submitAnswers($attempt, [
            $this->answerPayload($firstQuestion, $this->correctOption($firstQuestion)),
            $this->answerPayload($secondQuestion, $this->wrongOption($secondQuestion)),
        ]);

        $this->assertDatabaseCount('student_exam_answers', 2);
        $this->assertNotNull($result->finished_at);
        $this->assertSame(1, $result->correct_answers_count);
        $this->assertSame(5.0, (float) $result->score);
    }

    public function test_it_blocks_submitting_answers_twice(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $student = $this->createStudent($classGroup);
        $exam = $this->createExam($classGroup);
        $attempt = $this->createAttempt($student, $exam);
        $questions = $exam->questions()->with('options')->get();
        $answers = $questions
            ->map(fn (ExamQuestion $question): array => $this->answerPayload($question, $this->correctOption($question)))
            ->all();

        $this->service(cacheFlushes: 1)->submitAnswers($attempt, $answers);

        $this->expectException(ValidationException::class);

        $this->service()->submitAnswers($attempt->fresh(), $answers);
    }

    public function test_it_rejects_answer_for_question_that_does_not_belong_to_exam(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $student = $this->createStudent($classGroup);
        $exam = $this->createExam($classGroup);
        $otherExam = $this->createExam($classGroup);
        $attempt = $this->createAttempt($student, $exam);
        $examQuestion = $exam->questions()->with('options')->first();
        $otherExamQuestion = $otherExam->questions()->with('options')->first();

        $this->expectException(ValidationException::class);

        $this->service()->submitAnswers($attempt, [
            $this->answerPayload($otherExamQuestion, $this->correctOption($otherExamQuestion)),
            $this->answerPayload($examQuestion, $this->correctOption($examQuestion)),
        ]);
    }

    public function test_it_rejects_option_that_does_not_belong_to_question(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $student = $this->createStudent($classGroup);
        $exam = $this->createExam($classGroup);
        $attempt = $this->createAttempt($student, $exam);
        [$firstQuestion, $secondQuestion] = $exam->questions()->with('options')->get();

        $this->expectException(ValidationException::class);

        $this->service()->submitAnswers($attempt, [
            $this->answerPayload($firstQuestion, $this->correctOption($secondQuestion)),
            $this->answerPayload($secondQuestion, $this->correctOption($secondQuestion)),
        ]);
    }

    private function service(int $cacheFlushes = 0): ExamExecutionService
    {
        $dashboardService = $this->createMock(ExamDashboardService::class);
        $dashboardService
            ->expects($this->exactly($cacheFlushes))
            ->method('forgetDashboardCache');

        return new ExamExecutionService($dashboardService);
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

    private function createQuestion(Exam $exam, string $statement, int $position): ExamQuestion
    {
        $question = $exam->questions()->create([
            'statement' => $statement,
            'position' => $position,
        ]);

        $question->options()->create([
            'description' => 'Correct option',
            'is_correct' => true,
        ]);
        $question->options()->create([
            'description' => 'Wrong option',
            'is_correct' => false,
        ]);

        return $question;
    }

    private function createAttempt(Student $student, Exam $exam): ExamAttempt
    {
        return ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => now(),
        ]);
    }

    private function answerPayload(ExamQuestion $question, ExamQuestionOption $option): array
    {
        return [
            'exam_question_id' => $question->id,
            'exam_question_option_id' => $option->id,
        ];
    }

    private function correctOption(ExamQuestion $question): ExamQuestionOption
    {
        return $question->options->firstWhere('is_correct', true);
    }

    private function wrongOption(ExamQuestion $question): ExamQuestionOption
    {
        return $question->options->firstWhere('is_correct', false);
    }
}
