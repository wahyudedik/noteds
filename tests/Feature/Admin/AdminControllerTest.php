<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Note;
use App\Models\Transaction;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $buyer;
    private User $seller;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->buyer = User::factory()->create(['role' => 'buyer']);
        $this->seller = User::factory()->create(['role' => 'seller']);

        // Assign permissions to admin
        $this->admin->givePermissionTo('view-admin-dashboard', 'manage-users', 'manage-notes', 'manage-transactions', 'manage-withdrawals', 'moderate-forum', 'view-reports', 'manage-settings');
    }

    /**
     * Test admin dashboard access
     */
    public function test_admin_dashboard_access(): void
    {
        // Non-admin should not access
        $response = $this->actingAs($this->buyer)->get('/admin/dashboard');
        $response->assertStatus(403);

        // Admin should access
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    /**
     * Test admin can list users
     */
    public function test_admin_can_list_users(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/users');

        $response->assertStatus(200);
        $response->assertViewHas('users');
        $response->assertViewHas('stats');
    }

    /**
     * Test admin can filter users by role
     */
    public function test_admin_can_filter_users_by_role(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/users?role=seller');

        $response->assertStatus(200);
        $this->assertTrue($response->viewData('users')->count() > 0);
    }

    /**
     * Test admin can verify user
     */
    public function test_admin_can_verify_user(): void
    {
        $unverifiedUser = User::factory()->create([
            'role' => 'buyer',
            'is_verified' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/users/{$unverifiedUser->id}/verify");

        $response->assertRedirect();
        $this->assertTrue($unverifiedUser->refresh()->is_verified);
    }

    /**
     * Test admin can ban user
     */
    public function test_admin_can_ban_user(): void
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/users/{$this->buyer->id}/ban", [
                'reason' => 'Spam behavior',
                'days' => 30,
            ]);

        $response->assertRedirect();
        $this->assertTrue($this->buyer->refresh()->is_banned);
        $this->assertEquals('Spam behavior', $this->buyer->ban_reason);
    }

    /**
     * Test admin can list notes
     */
    public function test_admin_can_list_notes(): void
    {
        Note::factory(5)->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/data-management/notes');

        $response->assertStatus(200);
        $response->assertViewHas('notes');
        $response->assertViewHas('stats');
    }

    /**
     * Test admin can approve note
     */
    public function test_admin_can_approve_note(): void
    {
        $note = Note::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->post("/admin/data-management/notes/{$note->id}/approve", [
                'notes' => 'Looks good!',
            ]);

        $response->assertRedirect();
        $this->assertEquals('published', $note->refresh()->status);
    }

    /**
     * Test admin can reject note
     */
    public function test_admin_can_reject_note(): void
    {
        $note = Note::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->post("/admin/data-management/notes/{$note->id}/reject", [
                'reason' => 'Inappropriate content',
            ]);

        $response->assertRedirect();
        $this->assertEquals('rejected', $note->refresh()->status);
    }

    /**
     * Test admin can list transactions
     */
    public function test_admin_can_list_transactions(): void
    {
        Transaction::factory(5)->create(['status' => 'completed']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/data-management/transactions');

        $response->assertStatus(200);
        $response->assertViewHas('transactions');
        $response->assertViewHas('stats');
    }

    /**
     * Test admin can filter transactions by status
     */
    public function test_admin_can_filter_transactions(): void
    {
        Transaction::factory(3)->create(['status' => 'completed']);
        Transaction::factory(2)->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/data-management/transactions?status=completed');

        $response->assertStatus(200);
        $transactions = $response->viewData('transactions');
        $this->assertTrue($transactions->count() >= 3);
    }

    /**
     * Test admin can list withdrawals
     */
    public function test_admin_can_list_withdrawals(): void
    {
        Withdrawal::factory(5)->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/data-management/withdrawals');

        $response->assertStatus(200);
        $response->assertViewHas('withdrawals');
        $response->assertViewHas('stats');
    }

    /**
     * Test admin can approve withdrawal
     */
    public function test_admin_can_approve_withdrawal(): void
    {
        $withdrawal = Withdrawal::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->post("/admin/data-management/withdrawals/{$withdrawal->id}/approve", [
                'notes' => 'Approved',
            ]);

        $response->assertRedirect();
        $this->assertEquals('approved', $withdrawal->refresh()->status);
    }

    /**
     * Test admin can reject withdrawal
     */
    public function test_admin_can_reject_withdrawal(): void
    {
        $withdrawal = Withdrawal::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->post("/admin/data-management/withdrawals/{$withdrawal->id}/reject", [
                'reason' => 'Invalid bank account',
            ]);

        $response->assertRedirect();
        $this->assertEquals('rejected', $withdrawal->refresh()->status);
    }

    /**
     * Test non-admin cannot access admin routes
     */
    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $response = $this->actingAs($this->buyer)->get('/admin/dashboard');
        $response->assertStatus(403);

        $response = $this->actingAs($this->seller)->get('/admin/users');
        $response->assertStatus(403);

        $response = $this->actingAs($this->buyer)->get('/admin/data-management/notes');
        $response->assertStatus(403);
    }

    /**
     * Test banned user cannot access admin
     */
    public function test_banned_admin_cannot_access(): void
    {
        $this->admin->update(['is_banned' => true]);

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * Test admin can view reports
     */
    public function test_admin_can_view_revenue_report(): void
    {
        Transaction::factory(10)->create(['status' => 'completed']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/reports/revenue');

        $response->assertStatus(200);
        $response->assertViewHas('stats');
        $response->assertViewHas('dailyBreakdown');
    }

    /**
     * Test admin can export transactions
     */
    public function test_admin_can_export_transactions(): void
    {
        Transaction::factory(5)->create(['status' => 'completed']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/data-management/transactions/export/csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    /**
     * Test admin settings page
     */
    public function test_admin_can_view_settings(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/settings');

        $response->assertStatus(200);
        $response->assertViewIs('admin.settings.index');
    }

    /**
     * Test admin can update general settings
     */
    public function test_admin_can_update_general_settings(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/settings/general', [
                'app_name' => 'Noteds New',
                'app_url' => 'https://noteds-new.com',
                'app_description' => 'New description',
                'support_email' => 'support@noteds.com',
                'admin_email' => 'admin@noteds.com',
                'timezone' => 'Asia/Jakarta',
                'currency' => 'IDR',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Test validation on settings update
     */
    public function test_settings_validation(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/settings/payment', [
                'commission_percentage' => 50,
                'seller_percentage' => 30, // Should be 50 to make 100
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
