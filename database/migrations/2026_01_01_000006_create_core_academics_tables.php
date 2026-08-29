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
        // 1. Classes
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name'); // e.g. "Class 8", "Grade 10"
            $table->string('code')->nullable(); // "C8"
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'order_index']);
        });

        // 2. Sections
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->string('name'); // "A", "B", "Rose"
            $table->integer('capacity')->default(40);
            $table->foreignId('class_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'class_id']);
        });

        // 3. Subjects
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('school_classes')->cascadeOnDelete();
            $table->string('name'); // "Mathematics", "Science", "English"
            $table->string('code')->nullable(); // "MATH101"
            $table->enum('type', ['theory', 'practical', 'both'])->default('theory');
            $table->timestamps();

            $table->index(['tenant_id', 'class_id']);
        });

        // 4. Parents
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('occupation')->nullable();
            $table->string('relationship')->default('Parent'); // Father, Mother, Guardian
            $table->string('alternate_phone')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id']);
        });

        // 5. Students
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('parents')->nullOnDelete();
            $table->string('admission_number')->unique();
            $table->string('roll_number')->nullable();
            $table->date('dob')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('blood_group', 5)->nullable();
            $table->text('address')->nullable();
            $table->date('enrollment_date')->nullable();
            $table->enum('status', ['active', 'transferred', 'graduated', 'suspended'])->default('active');
            $table->timestamps();

            $table->index(['tenant_id', 'class_id', 'section_id']);
            $table->index(['tenant_id', 'parent_id']);
        });

        // 6. Teacher Allocations (Classes & Subjects)
        Schema::create('teacher_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('teacher_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'teacher_user_id']);
        });

        // 7. Attendances
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'holiday'])->default('present');
            $table->string('remarks')->nullable();
            $table->foreignId('marked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'student_id', 'date']);
            $table->index(['tenant_id', 'class_id', 'section_id', 'date']);
        });

        // 8. Timetables
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room_number')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'class_id', 'section_id', 'day_of_week']);
        });

        // 9. Homework
        Schema::create('homework', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('assigned_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->string('attachment_url')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'class_id', 'section_id', 'due_date']);
        });

        // 10. Homework Submissions
        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('homework_id')->constrained('homework')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->text('submission_text')->nullable();
            $table->string('attachment_url')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->enum('status', ['submitted', 'reviewed', 'late', 'resubmit_requested'])->default('submitted');
            $table->text('teacher_feedback')->nullable();
            $table->decimal('marks', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'homework_id', 'student_id']);
        });

        // 11. Notices
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->enum('audience_type', ['all', 'teachers', 'students', 'parents', 'class'])->default('all');
            $table->foreignId('target_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->useCurrent();
            $table->timestamps();

            $table->index(['tenant_id', 'is_published', 'published_at']);
        });

        // 12. Direct Messages (Live Chat / Threaded Communication)
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('sender_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message_body');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'sender_user_id', 'receiver_user_id']);
            $table->index(['tenant_id', 'receiver_user_id', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('notices');
        Schema::dropIfExists('homework_submissions');
        Schema::dropIfExists('homework');
        Schema::dropIfExists('timetables');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('teacher_allocations');
        Schema::dropIfExists('students');
        Schema::dropIfExists('parents');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('school_classes');
    }
};
