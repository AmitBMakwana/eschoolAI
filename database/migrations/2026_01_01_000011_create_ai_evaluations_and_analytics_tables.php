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
        // 1. AI Answer Sheet Evaluations
        Schema::create('ai_answer_sheet_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('submission_url')->nullable();
            $table->longText('extracted_text');
            $table->json('marking_rubric')->nullable();
            $table->json('question_evaluations')->nullable(); // Itemized marks & specific feedback per question
            $table->decimal('total_score_awarded', 8, 2);
            $table->decimal('total_possible_score', 8, 2);
            $table->json('strengths')->nullable();
            $table->json('areas_for_improvement')->nullable();
            $table->boolean('teacher_reviewed')->default(false);
            $table->decimal('final_score', 8, 2)->nullable();
            $table->enum('status', ['pending', 'evaluated', 'approved'])->default('evaluated');
            $table->timestamps();

            $table->index(['tenant_id', 'exam_id', 'student_id']);
            $table->index(['tenant_id', 'status']);
        });

        // 2. AI Student Longitudinal Performance Insights
        Schema::create('ai_student_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->string('academic_year', 20)->default('2026-2027');
            $table->enum('overall_trend', ['improving', 'steady', 'declining', 'at_risk'])->default('steady');
            $table->json('gpa_trajectory')->nullable(); // Term-by-term score history
            $table->json('attendance_correlation')->nullable();
            $table->json('strength_topics')->nullable();
            $table->json('struggling_topics')->nullable();
            $table->json('personalized_recommendations')->nullable();
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();

            $table->index(['tenant_id', 'student_id', 'academic_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_student_insights');
        Schema::dropIfExists('ai_answer_sheet_evaluations');
    }
};
