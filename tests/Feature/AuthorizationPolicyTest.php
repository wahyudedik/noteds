<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Note;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

/**
 * Authorization Policy Tests
 * 
 * Verifies that:
 * - Users can only access their own resources
 * - Proper role-based access control
 * - Admins can access everything
 * - Suspended users are denied access
 */
class AuthorizationPolicyTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private User $otherUser;
    private Note $note;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->note = Note::factory()->create(['user_id' => $this->user->id]);
    }

    /**
     * Test: User cannot edit other user's notes
     */
    public function test_user_cannot_edit_others_notes()
    {
        $this->actingAs($this->otherUser);

        $response = $this->put("/api/notes/{$this->note->id}", [
            'title' => 'Hacked Title',
            'content' => 'Hacked content',
        ]);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertDatabaseMissing('notes', ['id' => $this->note->id, 'title' => 'Hacked Title']);
    }

    /**
     * Test: User can edit own notes
     */
    public function test_user_can_edit_own_notes()
    {
        $this->actingAs($this->user);

        $response = $this->put("/api/notes/{$this->note->id}", [
            'title' => 'Updated Title',
            'content' => 'Updated content',
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->note->refresh();
        $this->assertEquals('Updated Title', $this->note->title);
    }

    /**
     * Test: User cannot delete other user's notes
     */
    public function test_user_cannot_delete_others_notes()
    {
        $this->actingAs($this->otherUser);

        $response = $this->delete("/api/notes/{$this->note->id}");

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertDatabaseHas('notes', ['id' => $this->note->id]);
    }

    /**
     * Test: Suspended user cannot perform actions
     */
    public function test_suspended_user_cannot_create_notes()
    {
        $this->user->update(['status' => 'suspended']);
        $this->actingAs($this->user);

        $response = $this->post('/api/notes', [
            'title' => 'New Note',
            'content' => 'Content',
        ]);

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * Test: Admin can view all notes
     */
    public function test_admin_can_view_all_notes()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $response = $this->get("/api/notes/{$this->note->id}");

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test: Unauthenticated user cannot create notes
     */
    public function test_unauthenticated_user_cannot_create_notes()
    {
        $response = $this->post('/api/notes', [
            'title' => 'New Note',
            'content' => 'Content',
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test: Rate limiting prevents excessive note creation
     */
    public function test_rate_limiting_on_note_creation()
    {
        $this->actingAs($this->user);

        // Create 20 notes quickly (at limit)
        for ($i = 0; $i < 20; $i++) {
            $this->post('/api/notes', [
                'title' => "Note $i",
                'content' => 'Content',
            ]);
        }

        // 21st attempt should be rate limited
        $response = $this->post('/api/notes', [
            'title' => 'Note 21',
            'content' => 'Content',
        ]);

        $this->assertEquals(429, $response->getStatusCode());
    }

    /**
     * Test: Only note owner can change visibility
     */
    public function test_only_owner_can_change_visibility()
    {
        $this->actingAs($this->otherUser);

        $response = $this->patch("/api/notes/{$this->note->id}/visibility", [
            'visibility' => 'public',
        ]);

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * Test: Non-seller cannot create transactions
     */
    public function test_non_seller_cannot_create_transactions()
    {
        $buyer = User::factory()->create();
        $buyer->removeRole('seller');

        $this->actingAs($buyer);

        $response = $this->post('/api/transactions', [
            'note_id' => $this->note->id,
            'amount' => 100,
        ]);

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * Test: Buyer without KYC cannot make transactions
     */
    public function test_kyc_required_for_transactions()
    {
        $buyer = User::factory()->create();
        $buyer->assignRole(['buyer', 'seller']);

        // User exists but KYC not verified
        $this->actingAs($buyer);

        $response = $this->post('/api/transactions', [
            'note_id' => $this->note->id,
            'amount' => 100,
        ]);

        // Should be rejected due to missing KYC
        $this->assertEquals(403, $response->getStatusCode());
    }
}
