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
        // 1. AI Generated Question Papers
        Schema::create('ai_generated_question_papers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('exam_paper_id')->nullable()->constrained('exam_papers')->nullOnDelete();
            $table->string('title');
            $table->integer('duration_minutes')->default(180);
            $table->decimal('total_marks', 8, 2)->default(100.00);
            $table->json('blueprint')->nullable(); // Bloom's breakdown & difficulty distribution
            $table->json('sections')->nullable(); // Sections A, B, C with questions
            $table->json('marking_scheme')->nullable(); // Scoring rubric
            $table->json('answer_key')->nullable(); // Complete answers
            $table->enum('status', ['draft', 'approved'])->default('draft');
            $table->timestamps();

            $table->index(['tenant_id', 'class_id', 'subject_id']);
        });

        // 2. AI Worksheets
        Schema::create('ai_worksheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('title');
            $table->string('topic');
            $table->enum('difficulty', ['easy', 'medium', 'hard', 'adaptive'])->default('medium');
            $table->text('instructions')->nullable();
            $table->json('content')->nullable(); // Structured sections with questions & exercises
            $table->json('solution_guide')->nullable(); // Teacher answer keys & explanations
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();

            $table->index(['tenant_id', 'class_id', 'subject_id']);
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_worksheets');
        Schema::dropIfExists('ai_generated_question_papers');
    }
};
