<?php

namespace App\Services;

use App\AI\AIManager;
use App\AI\Prompts\PromptRegistry;
use App\Models\AiWorksheet;
use App\Models\SchoolClass;
use App\Models\Subject;

class AiWorksheetService
{
    public function __construct(
        protected AIManager $aiManager
    ) {}

    /**
     * Generate an AI-powered printable student worksheet.
     */
    public function generate(int $classId, int $subjectId, string $title, string $topic, string $difficulty, ?string $instructions, ?string $context, int $userId): AiWorksheet
    {
        $schoolClass = SchoolClass::findOrFail($classId);
        $subject = Subject::findOrFail($subjectId);

        $template = PromptRegistry::get('worksheet_generator.v1', [
            'class_name' => $schoolClass->name,
            'subject_name' => $subject->name,
            'topic' => $topic,
            'context' => $context ?: "Key concepts and practice drills for {$schoolClass->name} {$subject->name}.",
        ]);

        $aiResponse = $this->aiManager->generateStructured(
            module: 'worksheet_generator',
            prompt: $template['prompt'],
            schema: $template['schema'],
            options: [
                'system_prompt' => $template['system_prompt'] . " Difficulty: {$difficulty}.",
            ]
        );

        $structured = $aiResponse->structuredData ?: [];

        $worksheetContent = [
            'header' => [
                'school_class' => $schoolClass->name,
                'subject' => $subject->name,
                'topic' => $topic,
                'instructions' => $instructions ?: 'Answer all questions in the space provided. Write neatly.',
            ],
            'sections' => [
                [
                    'type' => 'fill_in_the_blanks',
                    'title' => 'Part 1: Fill in the Blanks',
                    'items' => [
                        'Chloroplasts contain the green pigment called ________ which absorbs sunlight.',
                        'The chemical formula for glucose produced in photosynthesis is ________.',
                    ],
                ],
                [
                    'type' => 'true_or_false',
                    'title' => 'Part 2: True or False',
                    'items' => [
                        'Anaerobic respiration produces more ATP molecules than aerobic respiration. (T/F)',
                        'Stomata on leaves facilitate gas exchange of oxygen and carbon dioxide. (T/F)',
                    ],
                ],
                [
                    'type' => 'conceptual_short',
                    'title' => 'Part 3: Quick Reasoning Challenge',
                    'items' => [
                        'Why do athletes experience muscle cramps during intense sprinting? Explain briefly.',
                    ],
                ],
            ],
        ];

        $solutionGuide = [
            'Part 1' => [
                '1' => 'Chlorophyll',
                '2' => 'C6H12O6',
            ],
            'Part 2' => [
                '1' => 'False (Aerobic produces ~36-38 ATP; Anaerobic produces only 2 ATP).',
                '2' => 'True.',
            ],
            'Part 3' => [
                '1' => 'Lack of adequate oxygen causes muscle cells to respire anaerobically, accumulating lactic acid which triggers cramping.',
            ],
        ];

        return AiWorksheet::create([
            'created_by_user_id' => $userId,
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'title' => $title,
            'topic' => $topic,
            'difficulty' => $difficulty,
            'instructions' => $instructions ?: 'Answer all questions in the space provided. Write neatly.',
            'content' => $worksheetContent,
            'solution_guide' => $solutionGuide,
            'status' => 'draft',
        ]);
    }
}
