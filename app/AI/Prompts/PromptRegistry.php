<?php

namespace App\AI\Prompts;

class PromptRegistry
{
    /**
     * Get versioned prompt template by key.
     */
    public static function get(string $key, array $variables = []): array
    {
        $templates = [
            'lesson_planner.v1' => [
                'module' => 'lesson_planner',
                'version' => '1.0',
                'system_prompt' => 'You are an expert pedagogical curriculum designer. You construct grounded lesson plans aligned with educational standards.',
                'template' => "Create a detailed 45-minute lesson plan for Class {class_name}, Subject: {subject_name}.\nTopic: {topic}\n\nReference Material / Context:\n{context}\n\nInclude learning outcomes, prerequisite knowledge, step-by-step timed classroom activities, teaching aids, and 3 formative assessment questions.",
                'schema' => [
                    'title' => 'string',
                    'class' => 'string',
                    'subject' => 'string',
                    'duration_minutes' => 'integer',
                    'learning_outcomes' => 'array',
                    'prerequisites' => 'array',
                    'activities' => 'array',
                    'formative_assessments' => 'array',
                ],
            ],
            'question_generator.v1' => [
                'module' => 'question_generator',
                'version' => '1.0',
                'system_prompt' => 'You are an academic test designer for K-12 education.',
                'template' => "Generate {question_count} assessment questions for Class {class_name}, Subject: {subject_name}.\nTopic: {topic}\nDifficulty: {difficulty}\n\nContext:\n{context}",
                'schema' => [
                    'questions' => 'array',
                ],
            ],
            'circular_generator.v1' => [
                'module' => 'circular_generator',
                'version' => '1.0',
                'system_prompt' => 'You are a professional school communications director.',
                'template' => "Draft an institutional circular / announcement for School: {school_name}.\nAudience: {audience}\nEvent / Subject: {event_topic}\nDate & Details: {details}",
                'schema' => [
                    'title' => 'string',
                    'salutation' => 'string',
                    'body' => 'string',
                    'call_to_action' => 'string',
                    'signoff' => 'string',
                ],
            ],
            'worksheet_generator.v1' => [
                'module' => 'worksheet_generator',
                'version' => '1.0',
                'system_prompt' => 'You are an educational worksheet designer.',
                'template' => "Generate a printable student worksheet for Class {class_name}, Subject: {subject_name}.\nTopic: {topic}\n\nContext Chunks:\n{context}",
                'schema' => [
                    'worksheet_title' => 'string',
                    'sections' => 'array',
                ],
            ],
        ];

        if (!isset($templates[$key])) {
            throw new \InvalidArgumentException("Prompt template '{$key}' not found in PromptRegistry.");
        }

        $entry = $templates[$key];
        $prompt = $entry['template'];

        foreach ($variables as $var => $val) {
            $prompt = str_replace("{{$var}}", (string) $val, $prompt);
        }

        return [
            'prompt' => $prompt,
            'system_prompt' => $entry['system_prompt'],
            'schema' => $entry['schema'],
            'version' => $entry['version'],
            'module' => $entry['module'],
        ];
    }
}
