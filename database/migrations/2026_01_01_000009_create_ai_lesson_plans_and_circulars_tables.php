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
        // 1. AI Lesson Plans Table
        Schema::create('ai_lesson_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('topic');
            $table->integer('duration_minutes')->default(45);
            $table->string('prompt_version', 20)->default('1.0');
            $table->json('learning_outcomes')->nullable();
            $table->json('prerequisites')->nullable();
            $table->json('activities')->nullable();
            $table->json('teaching_aids')->nullable();
            $table->json('formative_assessments')->nullable();
            $table->json('homework_recommendations')->nullable();
            $table->longText('full_plan_text')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();

            $table->index(['tenant_id', 'class_id', 'subject_id']);
            $table->index(['tenant_id', 'status']);
        });

        // 2. AI Circulars Table
        Schema::create('ai_circulars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('notice_id')->nullable()->constrained('notices')->nullOnDelete();
            $table->string('title');
            $table->enum('audience', ['all', 'teachers', 'parents', 'students', 'staff'])->default('all');
            $table->string('event_topic');
            $table->enum('tone', ['formal', 'celebratory', 'urgent', 'welcoming'])->default('formal');
            $table->longText('generated_body');
            $table->longText('final_content')->nullable();
            $table->boolean('is_dispatched')->default(false);
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'audience', 'is_dispatched']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_circulars');
        Schema::dropIfExists('ai_lesson_plans');
    }
};
