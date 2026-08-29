<?php

namespace App\Services;

use App\AI\AIManager;
use App\AI\Prompts\PromptRegistry;
use App\Models\AiCircular;
use App\Models\Notice;
use App\Tenancy\TenantContext;

class CircularGeneratorService
{
    public function __construct(
        protected AIManager $aiManager
    ) {}

    /**
     * Generate an institutional circular draft.
     */
    public function generate(string $title, string $audience, string $eventTopic, string $tone, string $details, int $userId): AiCircular
    {
        $tenant = TenantContext::get();
        $schoolName = $tenant ? $tenant->name : 'Our School';

        $template = PromptRegistry::get('circular_generator.v1', [
            'school_name' => $schoolName,
            'audience' => ucfirst($audience),
            'event_topic' => $eventTopic,
            'details' => $details,
        ]);

        $aiResponse = $this->aiManager->generateStructured(
            module: 'circular_generator',
            prompt: $template['prompt'],
            schema: $template['schema'],
            options: [
                'system_prompt' => $template['system_prompt'] . " Tone: {$tone}.",
            ]
        );

        $structured = $aiResponse->structuredData ?: [];
        $body = $structured['body'] ?? $aiResponse->content;
        $callToAction = $structured['call_to_action'] ?? '';
        $signoff = $structured['signoff'] ?? "Warm regards,\nPrincipal & Administration Office";

        $fullContent = "OFFICIAL CIRCULAR: " . strtoupper($title) . "\n\n"
            . "To: " . ucfirst($audience) . "\n\n"
            . $body . "\n\n"
            . ($callToAction ? "Action Required: " . $callToAction . "\n\n" : "")
            . $signoff;

        return AiCircular::create([
            'created_by_user_id' => $userId,
            'title' => $title,
            'audience' => $audience,
            'event_topic' => $eventTopic,
            'tone' => $tone,
            'generated_body' => $body,
            'final_content' => $fullContent,
            'is_dispatched' => false,
        ]);
    }

    /**
     * Dispatch circular to the official Notice Board.
     */
    public function dispatch(AiCircular $circular, int $userId): Notice
    {
        $notice = Notice::create([
            'title' => $circular->title,
            'content' => $circular->final_content ?: $circular->generated_body,
            'audience_type' => $circular->audience,
            'created_by_user_id' => $userId,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $circular->update([
            'notice_id' => $notice->id,
            'is_dispatched' => true,
            'dispatched_at' => now(),
        ]);

        return $notice;
    }
}
