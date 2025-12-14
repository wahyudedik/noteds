<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Input Sanitization & XSS Prevention Tests
 * 
 * Verifies that:
 * - Script tags are removed
 * - Event handlers are stripped
 * - HTML is properly escaped
 * - Dangerous content is filtered
 */
class InputSanitizationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /**
     * Test: Script tags are removed from input
     */
    public function test_script_tags_removed_from_input()
    {
        $response = $this->post('/api/notes', [
            'title' => 'My Note<script>alert("XSS")</script>',
            'content' => 'Some content',
        ]);

        $note = Note::latest()->first();
        $this->assertStringNotContainsString('<script>', $note->title);
        $this->assertStringNotContainsString('alert', $note->title);
    }

    /**
     * Test: Event handlers are stripped
     */
    public function test_event_handlers_stripped()
    {
        $response = $this->post('/api/notes', [
            'title' => 'My <img src=x onerror="alert(1)">',
            'content' => 'Content',
        ]);

        $note = Note::latest()->first();
        $this->assertStringNotContainsString('onerror', $note->title);
    }

    /**
     * Test: Iframe tags are removed
     */
    public function test_iframe_tags_removed()
    {
        $response = $this->post('/api/notes', [
            'title' => 'Title',
            'content' => '<iframe src="http://evil.com"></iframe>Malicious',
        ]);

        $note = Note::latest()->first();
        $this->assertStringNotContainsString('<iframe', $note->content);
    }

    /**
     * Test: JavaScript protocol is blocked
     */
    public function test_javascript_protocol_blocked()
    {
        $response = $this->post('/api/notes', [
            'title' => 'Title',
            'content' => '<a href="javascript:alert(1)">Click</a>',
        ]);

        $note = Note::latest()->first();
        $this->assertStringNotContainsString('javascript:', $note->content);
    }

    /**
     * Test: Null bytes are removed
     */
    public function test_null_bytes_removed()
    {
        $response = $this->post('/api/notes', [
            'title' => "My\x00Note",
            'content' => 'Content',
        ]);

        $note = Note::latest()->first();
        $this->assertStringNotContainsString("\x00", $note->title);
    }

    /**
     * Test: Control characters are removed
     */
    public function test_control_characters_removed()
    {
        $response = $this->post('/api/notes', [
            'title' => "My\x01\x02Note",
            'content' => 'Content',
        ]);

        $note = Note::latest()->first();
        $this->assertStringNotContainsString("\x01", $note->title);
        $this->assertStringNotContainsString("\x02", $note->title);
    }

    /**
     * Test: HTML entities are preserved in safe fields
     */
    public function test_html_entities_preserved()
    {
        $response = $this->post('/api/notes', [
            'title' => 'My &amp; Note',
            'content' => 'Content &lt;with&gt; entities',
        ]);

        $note = Note::latest()->first();
        // Should be decoded (not double-encoded)
        $this->assertStringContainsString('&', $note->title);
    }

    /**
     * Test: Email field never contains HTML
     */
    public function test_email_field_has_no_html()
    {
        $response = $this->put('/profile', [
            'email' => 'test<script>alert(1)</script>@example.com',
            'name' => 'Test User',
        ]);

        $user = $this->user->fresh();
        $this->assertFalse(strpos($user->email, '<script>'));
    }

    /**
     * Test: Form tags and inputs are removed
     */
    public function test_form_tags_removed()
    {
        $response = $this->post('/api/notes', [
            'title' => 'Title',
            'content' => '<form method="POST"><input name="redirect" value="evil"></form>',
        ]);

        $note = Note::latest()->first();
        $this->assertStringNotContainsString('<form', $note->content);
        $this->assertStringNotContainsString('<input', $note->content);
    }
}
