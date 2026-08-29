<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Exam Terms (e.g. "Term 1 - Mid Term 2026", "Final Examination 2026-27")
        Schema::create('exam_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('academic_year', 20)->default('2026-2027');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['upcoming', 'ongoing', 'completed', 'published'])->default('upcoming');
            $table->timestamps();

            $table->index(['tenant_id', 'academic_year', 'status']);
        });

        // 2. Grading Scales
        Schema::create('grading_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('grade'); // "A+", "A", "B", "C", "F"
            $table->decimal('min_percentage', 5, 2); // e.g. 90.00
            $table->decimal('max_percentage', 5, 2); // e.g. 100.00
            $table->decimal('grade_point', 3, 2)->default(4.00); // e.g. 4.0, 3.7
            $table->string('description')->nullable(); // "Outstanding", "Excellent"
            $table->timestamps();

            $table->index(['tenant_id', 'min_percentage', 'max_percentage']);
        });

        // 3. Exams
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('exam_term_id')->constrained('exam_terms')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('title'); // "Physics Mid-Term Exam"
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('total_marks', 5, 2)->default(100.00);
            $table->decimal('passing_marks', 5, 2)->default(35.00);
            $table->string('room_number')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'exam_term_id', 'class_id', 'subject_id']);
        });

        // 4. Question Bank
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->string('topic')->nullable(); // "Thermodynamics", "Optics"
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->enum('question_type', ['mcq', 'short_answer', 'essay', 'numerical', 'true_false'])->default('mcq');
            $table->text('question_text');
            $table->json('options')->nullable(); // Array of choices for MCQ
            $table->text('correct_answer');
            $table->text('explanation')->nullable();
            $table->decimal('marks', 4, 2)->default(1.00);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'subject_id', 'difficulty', 'question_type']);
        });

        // 5. Exam Papers (Composed assessments)
        Schema::create('exam_papers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->decimal('total_marks', 5, 2)->default(100.00);
            $table->integer('duration_minutes')->default(180);
            $table->json('sections')->nullable(); // Structured sections with questions and weightages
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'subject_id', 'class_id']);
        });

        // 6. Exam Marks Entry
        Schema::create('exam_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('marks_obtained', 5, 2)->default(0.00);
            $table->boolean('is_absent')->default(false);
            $table->string('grade', 10)->nullable(); // Auto-computed Grade (A+, A, etc.)
            $table->decimal('grade_point', 3, 2)->nullable(); // Auto-computed Grade Point (4.0, 3.5)
            $table->text('remarks')->nullable();
            $table->foreignId('entered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'exam_id', 'student_id']);
            $table->index(['tenant_id', 'exam_id']);
        });

        // 7. Consolidated Report Cards
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('exam_term_id')->constrained('exam_terms')->cascadeOnDelete();
            $table->decimal('total_max_marks', 6, 2);
            $table->decimal('total_marks_obtained', 6, 2);
            $table->decimal('percentage', 5, 2);
            $table->string('overall_grade', 10);
            $table->decimal('gpa', 3, 2)->default(0.00);
            $table->integer('class_rank')->nullable();
            $table->decimal('attendance_percentage', 5, 2)->nullable();
            $table->json('subject_breakdown'); // Itemized subjects, marks, grades, and teacher remarks
            $table->text('teacher_remarks')->nullable();
            $table->text('principal_remarks')->nullable();
            $table->enum('status', ['draft', 'published'])->default('published');
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();

            $table->unique(['tenant_id', 'student_id', 'exam_term_id']);
            $table->index(['tenant_id', 'exam_term_id', 'percentage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_cards');
        Schema::dropIfExists('exam_marks');
        Schema::dropIfExists('exam_papers');
        Schema::dropIfExists('question_banks');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('grading_scales');
        Schema::dropIfExists('exam_terms');
    }
};
