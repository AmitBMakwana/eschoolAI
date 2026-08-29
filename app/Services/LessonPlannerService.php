<?php

namespace App\Services;

use App\AI\AIManager;
use App\AI\Prompts\PromptRegistry;
use App\Models\AiLessonPlan;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Tenancy\TenantContext;

class LessonPlannerService
{
    public function __construct(
        protected AIManager $aiManager
    ) {}

    /**
     * Generate an AI-powered pedagogical lesson plan.
     */
    public function generate(int $classId, int $subjectId, string $topic, int $durationMinutes, ?string $context, int $userId): AiLessonPlan
    {
        $schoolClass = SchoolClass::findOrFail($classId);
        $subject = Subject::findOrFail($subjectId);

        $template = PromptRegistry::get('lesson_planner.v1', [
            'class_name' => $schoolClass->name,
            'subject_name' => $subject->name,
            'topic' => $topic,
            'context' => $context ?: "Standard K-12 national syllabus guidelines for {$schoolClass->name} {$subject->name}.",
        ]);

        $aiResponse = $this->aiManager->generateStructured(
            module: 'lesson_planner',
            prompt: $template['prompt'],
            schema: $template['schema'],
            options: [
                'system_prompt' => $template['system_prompt'],
            ]
        );

        $structured = $aiResponse->structuredData ?: [];

        return AiLessonPlan::create([
            'created_by_user_id' => $userId,
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'topic' => $topic,
            'duration_minutes' => $durationMinutes,
            'prompt_version' => $template['version'],
            'learning_outcomes' => $structured['learning_objectives'] ?? $structured['learning_outcomes'] ?? ['Grasp key conceptual fundamentals', 'Apply concepts in problem solving'],
            'prerequisites' => $structured['prerequisites'] ?? ['Prior grade foundational knowledge'],
            'activities' => $structured['activities'] ?? [
                ['time' => '0-10m', 'activity' => 'Interactive Inquiry & Introduction'],
                ['time' => '10-30m', 'activity' => 'Direct Instruction & Experimentation'],
                ['time' => '30-45m', 'activity' => 'Formative Assessment & Recap'],
            ],
            'teaching_aids' => $structured['teaching_aids'] ?? ['Whiteboard', 'Digital Presentation / Lab Kit'],
            'formative_assessments' => $structured['assessment_questions'] ?? $structured['formative_assessments'] ?? [],
            'homework_recommendations' => $structured['homework_recommendations'] ?? ['Review textbook summary notes'],
            'full_plan_text' => $aiResponse->content,
            'status' => 'draft',
        ]);
    }
}
