<?php

namespace Tests\Feature;

use Tests\TestCase;

class DesignSystemTest extends TestCase
{
    public function test_showcase_page_loads_successfully(): void
    {
        $response = $this->get('/showcase');

        $response->assertStatus(200)
            ->assertSee('Design System', false)
            ->assertSee('Component Showcase', false)
            ->assertSee('AI Education', false)
            ->assertSee('Grounded AI Lesson Planner', false)
            ->assertSee('Student Directory', false);
    }

    public function test_design_system_stylesheet_is_accessible(): void
    {
        $this->assertFileExists(public_path('css/schoolos-design-system.css'));
        $this->assertFileExists(public_path('js/schoolos-ui.js'));
    }
}
