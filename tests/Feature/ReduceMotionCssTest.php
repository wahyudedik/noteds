<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReduceMotionCssTest extends TestCase
{
    public function test_css_contains_reduce_motion_rules(): void
    {
        $path = base_path('resources/css/app.css');
        $this->assertFileExists($path);
        $css = file_get_contents($path);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $this->assertStringContainsString('html.rm-full *', $css);
        $this->assertStringContainsString('html.rm-medium *', $css);
        $this->assertStringContainsString('html.rm-light *', $css);
    }
}
