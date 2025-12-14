<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Authentication Security Tests
 * 
 * Tests for:
 * - Login rate limiting
 * - Account status verification
 * - CSRF token validation
 * - Session management
 * - Audit logging of login attempts
 */
class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['password' => bcrypt('password123')]);
    }

    /**
     * Test: Login is rate limited after 5 failed attempts in 15 minutes
     */
    public function test_login_rate_limiting()
    {
        $email = $this->user->email;

        // First 5 attempts should be allowed
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => $email,
                'password' => 'wrong_password',
            ]);

            $this->assertNotEquals(429, $response->getStatusCode());
        }

        // 6th attempt should be rate limited
        $response = $this->post('/login', [
            'email' => $email,
            'password' => 'wrong_password',
        ]);

        $this->assertEquals(429, $response->getStatusCode());
    }

    /**
     * Test: Successful login audit log is created
     */
    public function test_successful_login_creates_audit_log()
    {
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        // Check audit log was created
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'action' => 'login',
        ]);
    }

    /**
     * Test: Failed login attempt is logged
     */
    public function test_failed_login_creates_audit_log()
    {
        $email = $this->user->email;

        $this->post('/login', [
            'email' => $email,
            'password' => 'wrong_password',
        ]);

        // Failed login should be logged
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'failed_login',
            'data->email' => $email,
        ]);
    }

    /**
     * Test: Cannot login to suspended account
     */
    public function test_cannot_login_to_suspended_account()
    {
        $this->user->update(['status' => 'suspended']);

        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        $this->assertEquals(302, $response->getStatusCode()); // Redirect to login
        $this->assertGuest();
    }

    /**
     * Test: Cannot login to inactive account
     */
    public function test_cannot_login_to_inactive_account()
    {
        $this->user->update(['status' => 'inactive']);

        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        $this->assertGuest();
    }

    /**
     * Test: CSRF token is required for login
     */
    public function test_csrf_token_required_for_login()
    {
        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ], ['X-CSRF-Token' => 'invalid']);

        $this->assertEquals(419, $response->getStatusCode()); // Token mismatch
    }

    /**
     * Test: Session is regenerated after login
     */
    public function test_session_regenerated_after_login()
    {
        $oldSessionId = session()->getId();

        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        $newSessionId = session()->getId();

        // Session ID should be different
        $this->assertNotEquals($oldSessionId, $newSessionId);
    }
}
