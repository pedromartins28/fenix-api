<?php

namespace Tests\Unit;

use App\Models\ClassGroup;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\User;
use App\Services\ClassGroupExamService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ClassGroupExamServiceTest extends TestCase
{
    use RefreshDatabase;

    private ClassGroupExamService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ClassGroupExamService::class);
    }

    public function test_it_lists_exams_linked_to_class_group(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $otherClassGroup = ClassGroup::create(['code' => 'CLASS-B']);
        $linkedExam = $this->createExam('Linked Exam');
        $otherExam = $this->createExam('Other Exam');
        $classGroup->exams()->attach($linkedExam);
        $otherClassGroup->exams()->attach($otherExam);

        $exams = $this->service->listExams($classGroup);

        $this->assertCount(1, $exams);
        $this->assertSame($linkedExam->id, $exams->first()->id);
    }

    public function test_it_attaches_exam_to_class_group(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $exam = $this->createExam();

        $attachedExam = $this->service->attachExam($classGroup, $exam->id);

        $this->assertSame($exam->id, $attachedExam->id);
        $this->assertDatabaseHas('class_group_exam', [
            'class_group_id' => $classGroup->id,
            'exam_id' => $exam->id,
        ]);
        $this->assertTrue($attachedExam->classGroups->contains('id', $classGroup->id));
    }

    public function test_it_blocks_attaching_same_exam_twice(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $exam = $this->createExam();
        $classGroup->exams()->attach($exam);

        $this->expectException(ValidationException::class);

        $this->service->attachExam($classGroup, $exam->id);
    }

    public function test_it_detaches_exam_without_attempts_from_class_group(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $exam = $this->createExam();
        $classGroup->exams()->attach($exam);

        $this->service->detachExam($classGroup, $exam);

        $this->assertDatabaseMissing('class_group_exam', [
            'class_group_id' => $classGroup->id,
            'exam_id' => $exam->id,
        ]);
    }

    public function test_it_blocks_detaching_exam_attempted_by_student_from_same_class_group(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $exam = $this->createExam();
        $classGroup->exams()->attach($exam);
        $student = $this->createStudent($classGroup);
        $this->createAttempt($student, $exam);

        $this->expectException(ValidationException::class);

        $this->service->detachExam($classGroup, $exam);
    }

    public function test_it_detaches_exam_when_attempt_exists_only_for_another_class_group(): void
    {
        $classGroup = ClassGroup::create(['code' => 'CLASS-A']);
        $otherClassGroup = ClassGroup::create(['code' => 'CLASS-B']);
        $exam = $this->createExam();
        $classGroup->exams()->attach($exam);
        $otherStudent = $this->createStudent($otherClassGroup);
        $this->createAttempt($otherStudent, $exam);

        $this->service->detachExam($classGroup, $exam);

        $this->assertDatabaseMissing('class_group_exam', [
            'class_group_id' => $classGroup->id,
            'exam_id' => $exam->id,
        ]);
    }

    private function createExam(string $name = 'Math Exam'): Exam
    {
        return Exam::create([
            'name' => $name,
            'questions_count' => 2,
            'value' => 10,
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

    private function createAttempt(Student $student, Exam $exam): ExamAttempt
    {
        return ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => now(),
        ]);
    }
}
