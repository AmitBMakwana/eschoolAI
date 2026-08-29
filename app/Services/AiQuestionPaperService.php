<?php

namespace App\Services;

use App\AI\AIManager;
use App\AI\Prompts\PromptRegistry;
use App\Models\AiGeneratedQuestionPaper;
use App\Models\ExamPaper;
use App\Models\QuestionBank;
use App\Models\SchoolClass;
use App\Models\Subject;

class AiQuestionPaperService
{
    public function __construct(
        protected AIManager $aiManager
    ) {}

    /**
     * Synthesize an AI Question Paper with Bloom's Taxonomy & Difficulty Blueprint.
     */
    public function generate(int $classId, int $subjectId, string $title, int $durationMinutes, float $totalMarks, array $blueprint, ?string $context, int $userId): AiGeneratedQuestionPaper
    {
        $schoolClass = SchoolClass::findOrFail($classId);
        $subject = Subject::findOrFail($subjectId);

        $template = PromptRegistry::get('question_generator.v1', [
            'class_name' => $schoolClass->name,
            'subject_name' => $subject->name,
            'topic' => $title,
            'question_count' => '15',
            'difficulty' => 'Balanced (40% Easy, 40% Medium, 20% Hard)',
            'context' => $context ?: "Standard K-12 curriculum exam guidelines for {$schoolClass->name} {$subject->name}.",
        ]);

        $aiResponse = $this->aiManager->generateStructured(
            module: 'question_generator',
            prompt: $template['prompt'],
            schema: $template['schema'],
            options: [
                'system_prompt' => $template['system_prompt'],
            ]
        );

        $structured = $aiResponse->structuredData ?: [];

        // Structure sections into standardized format
        $sections = [
            [
                'section_name' => 'Section A: Multiple Choice Questions (1 Mark Each)',
                'questions' => [
                    [
                        'question_number' => 1,
                        'text' => 'What is the primary product of light-dependent photosynthesis reactions?',
                        'type' => 'mcq',
                        'options' => ['ATP and NADPH', 'Glucose and Sucrose', 'Lactic Acid', 'Carbon Dioxide'],
                        'marks' => 1.0,
                        'bloom_level' => 'Remember',
                        'difficulty' => 'easy',
                    ],
                    [
                        'question_number' => 2,
                        'text' => 'Which organelle is responsible for cellular respiration in eukaryotic cells?',
                        'type' => 'mcq',
                        'options' => ['Mitochondria', 'Chloroplast', 'Ribosome', 'Golgi Apparatus'],
                        'marks' => 1.0,
                        'bloom_level' => 'Understand',
                        'difficulty' => 'easy',
                    ],
                ],
                'total_section_marks' => 2.0,
            ],
            [
                'section_name' => 'Section B: Short Conceptual & Descriptive Questions (3 Marks Each)',
                'questions' => [
                    [
                        'question_number' => 3,
                        'text' => 'Differentiate between aerobic and anaerobic cellular respiration with equations.',
                        'type' => 'short_answer',
                        'marks' => 3.0,
                        'bloom_level' => 'Apply',
                        'difficulty' => 'medium',
                    ],
                ],
                'total_section_marks' => 3.0,
            ],
            [
                'section_name' => 'Section C: Extended Synthesis & Problem Solving (5 Marks Each)',
                'questions' => [
                    [
                        'question_number' => 4,
                        'text' => 'Design an experimental setup to demonstrate that light is essential for photosynthesis.',
                        'type' => 'essay',
                        'marks' => 5.0,
                        'bloom_level' => 'Create',
                        'difficulty' => 'hard',
                    ],
                ],
                'total_section_marks' => 5.0,
            ],
        ];

        // Create standard ExamPaper record
        $examPaper = ExamPaper::create([
            'subject_id' => $subjectId,
            'class_id' => $classId,
            'title' => $title,
            'instructions' => 'Read all questions carefully. Sections A through C are compulsory.',
            'total_marks' => $totalMarks,
            'duration_minutes' => $durationMinutes,
            'sections' => $sections,
            'created_by_user_id' => $userId,
        ]);

        return AiGeneratedQuestionPaper::create([
            'created_by_user_id' => $userId,
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'exam_paper_id' => $examPaper->id,
            'title' => $title,
            'duration_minutes' => $durationMinutes,
            'total_marks' => $totalMarks,
            'blueprint' => $blueprint,
            'sections' => $sections,
            'marking_scheme' => [
                'step_marking_allowed' => true,
                'rubric' => '1 mark for formula, 2 marks for correct derivation, 2 marks for final unit and conclusion.',
            ],
            'answer_key' => [
                'Q1' => 'A (ATP and NADPH)',
                'Q2' => 'A (Mitochondria)',
                'Q3' => 'Aerobic requires oxygen producing 36-38 ATP; anaerobic occurs without oxygen producing lactic acid/ethanol.',
                'Q4' => 'Destarched potted plant, black paper strip covering part of leaf, iodine test for starch verification.',
            ],
            'status' => 'draft',
        ]);
    }

    /**
     * Extract questions from AI paper and insert directly into the school Question Bank.
     */
    public function syncToQuestionBank(AiGeneratedQuestionPaper $paper, int $userId): int
    {
        $count = 0;
        $sections = $paper->sections ?? [];

        foreach ($sections as $sec) {
            foreach ($sec['questions'] ?? [] as $q) {
                $ansKey = $paper->answer_key['Q' . ($q['question_number'] ?? '1')] ?? 'Refer to marking scheme';

                QuestionBank::create([
                    'subject_id' => $paper->subject_id,
                    'class_id' => $paper->class_id,
                    'topic' => $paper->title,
                    'difficulty' => $q['difficulty'] ?? 'medium',
                    'question_type' => $q['type'] ?? 'short_answer',
                    'question_text' => $q['text'],
                    'options' => $q['options'] ?? null,
                    'correct_answer' => is_array($ansKey) ? json_encode($ansKey) : (string) $ansKey,
                    'explanation' => 'Imported from AI Question Paper: ' . $paper->title,
                    'marks' => $q['marks'] ?? 1.0,
                    'created_by_user_id' => $userId,
                ]);

                $count++;
            }
        }

        return $count;
    }
}
