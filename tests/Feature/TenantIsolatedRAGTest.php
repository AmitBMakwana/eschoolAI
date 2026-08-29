<?php

namespace Tests\Feature;

use App\Models\RagChunk;
use App\Models\RagDocument;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolatedRAGTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $schoolA;
    protected Tenant $schoolB;
    protected User $teacherSchoolA;
    protected User $teacherSchoolB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->schoolA = Tenant::where('subdomain', 'greenfield')->firstOrFail();
        $this->schoolB = Tenant::where('subdomain', 'oakridge')->firstOrFail();
        $this->teacherSchoolA = User::where('email', 'teacher@greenfield.edu')->firstOrFail();
        $this->teacherSchoolB = User::where('email', 'teacher@oakridge.edu')->firstOrFail();
    }

    public function test_school_a_can_ingest_and_query_rag_document(): void
    {
        $science = Subject::where('name', 'Science')->firstOrFail();
        $class8 = SchoolClass::where('name', 'Class 8')->firstOrFail();

        $textbookContent = "Chapter 12: Principles of Electricity and Electromagnetic Induction.\n\n"
            . "Michael Faraday demonstrated that when a magnetic flux linking a circuit changes, an electromotive force is induced in the circuit. "
            . "The magnitude of the induced EMF is directly proportional to the rate of change of magnetic flux. "
            . "Lenz's law further specifies that the direction of the induced current is always such as to oppose the change in magnetic flux that produced it.\n\n"
            . "Applications of induction include electric generators, transformers, and induction cooktops used in modern kitchens.";

        // 1. Ingest document
        $ingestRes = $this->actingAs($this->teacherSchoolA)
            ->postJson('/api/v1/rag/documents/upload', [
                'title' => 'NCERT Grade 8 Physics - Chapter 12 Electricity & Magnetism',
                'content_text' => $textbookContent,
                'class_id' => $class8->id,
                'subject_id' => $science->id,
                'document_type' => 'textbook',
            ]);

        $ingestRes->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'NCERT Grade 8 Physics - Chapter 12 Electricity & Magnetism',
                    'status' => 'indexed',
                ]
            ]);

        $docId = $ingestRes->json('data.id');
        $this->assertGreaterThan(0, $ingestRes->json('data.total_chunks'));

        // 2. Query document chunks
        $chunksRes = $this->actingAs($this->teacherSchoolA)
            ->getJson("/api/v1/rag/documents/{$docId}/chunks");

        $chunksRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertNotEmpty($chunksRes->json('data.chunks'));

        // 3. Semantic search query
        $queryRes = $this->actingAs($this->teacherSchoolA)
            ->postJson('/api/v1/rag/query', [
                'query' => 'Faraday law electromagnetic induction',
                'top_k' => 2,
            ]);

        $queryRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertNotEmpty($queryRes->json('data.matched_chunks'));
        $this->assertStringContainsString('Faraday', $queryRes->json('data.matched_chunks.0.content'));
    }

    public function test_rag_guarantees_strict_tenant_isolation_between_schools(): void
    {
        // 1. School A ingests Physics Textbook
        TenantContext::set($this->schoolA);
        $docA = $this->actingAs($this->teacherSchoolA)
            ->postJson('/api/v1/rag/documents/upload', [
                'title' => 'School A Proprietary Advanced Physics Guide',
                'content_text' => 'Confidential Quantum Mechanics and Special Relativity notes for School A honors track.',
                'document_type' => 'study_material',
            ]);
        $docA->assertStatus(201);

        // 2. School B ingests French Language Notes
        TenantContext::set($this->schoolB);
        $docB = $this->actingAs($this->teacherSchoolB)
            ->postJson('/api/v1/rag/documents/upload', [
                'title' => 'School B French Grammar Guide',
                'content_text' => 'Le subjonctif et la concordance des temps en langue francaise.',
                'document_type' => 'notes',
            ]);
        $docB->assertStatus(201);

        // 3. School A queries "French Grammar" -> Must NOT find School B chunks
        TenantContext::set($this->schoolA);
        $searchA = $this->actingAs($this->teacherSchoolA)
            ->postJson('/api/v1/rag/query', [
                'query' => 'French Grammar subjonctif',
            ]);

        $searchA->assertStatus(200);
        foreach ($searchA->json('data.matched_chunks') as $chunk) {
            $this->assertStringNotContainsString('Le subjonctif', $chunk['content']);
        }

        // 4. School B queries "Quantum Mechanics" -> Must NOT find School A chunks
        TenantContext::set($this->schoolB);
        $searchB = $this->actingAs($this->teacherSchoolB)
            ->postJson('/api/v1/rag/query', [
                'query' => 'Quantum Mechanics and Special Relativity',
            ]);

        $searchB->assertStatus(200);
        foreach ($searchB->json('data.matched_chunks') as $chunk) {
            $this->assertStringNotContainsString('Quantum Mechanics and Special Relativity', $chunk['content']);
        }
    }
}
