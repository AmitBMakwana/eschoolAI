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
        // 1. RAG Documents Table
        Schema::create('rag_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->string('title');
            $table->enum('document_type', ['textbook', 'syllabus', 'study_material', 'exam_paper', 'notes'])->default('study_material');
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->string('mime_type', 100)->default('text/plain');
            $table->integer('total_chunks')->default(0);
            $table->enum('status', ['uploading', 'processing', 'indexed', 'failed'])->default('processing');
            $table->timestamps();

            $table->index(['tenant_id', 'class_id', 'subject_id']);
            $table->index(['tenant_id', 'status']);
        });

        // 2. RAG Chunks Table
        Schema::create('rag_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('rag_document_id')->constrained('rag_documents')->cascadeOnDelete();
            $table->integer('chunk_index')->default(0);
            $table->longText('content');
            $table->integer('token_count')->default(0);
            $table->string('vector_id')->nullable(); // Qdrant Point UUID
            $table->json('metadata')->nullable(); // Page number, chapter, topic
            $table->timestamps();

            $table->index(['tenant_id', 'rag_document_id']);
            $table->index(['tenant_id', 'vector_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rag_chunks');
        Schema::dropIfExists('rag_documents');
    }
};
