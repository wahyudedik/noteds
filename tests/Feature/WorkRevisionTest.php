namespace Tests\Feature;

use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\WorkRevision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkRevisionTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $vendor;
    private ServiceOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create(['role' => 'buyer']);
        $this->vendor = User::factory()->create(['role' => 'vendor']);
        
        $this->order = ServiceOrder::factory()->create([
            'buyer_id' => $this->buyer->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'submitted',
            'max_revisions' => 3,
        ]);
    }

    /** @test */
    public function buyer_can_request_revision()
    {
        $this->actingAs($this->buyer);

        $response = $this->post(route('studio.orders.request-revision', $this->order), [
            'reason' => 'Please adjust the colors to be more vibrant',
        ]);

        $this->assertDatabaseHas('work_revisions', [
            'service_order_id' => $this->order->id,
            'requested_by' => $this->buyer->id,
            'request_reason' => 'Please adjust the colors to be more vibrant',
            'status' => 'pending',
        ]);

        $response->assertRedirect();
    }

    /** @test */
    public function vendor_can_submit_revision()
    {
        $revision = WorkRevision::factory()->create([
            'service_order_id' => $this->order->id,
            'requested_by' => $this->buyer->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->vendor);

        $response = $this->post(route('studio.revisions.submit', $revision), [
            'submission_notes' => 'Adjusted colors as requested. Please review.',
        ]);

        $revision->refresh();
        $this->assertEquals('submitted', $revision->status);
        $this->assertNotNull($revision->submitted_at);
        $this->assertEquals('Adjusted colors as requested. Please review.', $revision->submission_notes);
    }

    /** @test */
    public function buyer_can_approve_revision()
    {
        $revision = WorkRevision::factory()->create([
            'service_order_id' => $this->order->id,
            'requested_by' => $this->buyer->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->actingAs($this->buyer);

        $response = $this->post(route('studio.revisions.approve', $revision));

        $revision->refresh();
        $this->assertEquals('accepted', $revision->status);
        $this->order->refresh();
        $this->assertEquals('approved', $this->order->status);
    }

    /** @test */
    public function buyer_can_reject_revision()
    {
        $revision = WorkRevision::factory()->create([
            'service_order_id' => $this->order->id,
            'requested_by' => $this->buyer->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->actingAs($this->buyer);

        $response = $this->post(route('studio.revisions.reject', $revision), [
            'rejection_reason' => 'Colors are still not vibrant enough',
        ]);

        $revision->refresh();
        $this->assertEquals('rejected', $revision->status);
        $this->assertNotNull($revision->rejected_at);
        $this->assertEquals('Colors are still not vibrant enough', $revision->rejection_reason);
    }

    /** @test */
    public function cannot_request_revision_exceeding_max_limit()
    {
        $this->order->update(['max_revisions' => 1]);

        WorkRevision::factory()->create([
            'service_order_id' => $this->order->id,
            'revision_number' => 1,
            'status' => 'accepted',
        ]);

        $this->actingAs($this->buyer);

        $response = $this->post(route('studio.orders.request-revision', $this->order), [
            'reason' => 'Another revision',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function revision_count_increments_correctly()
    {
        $this->actingAs($this->buyer);

        for ($i = 0; $i < 3; $i++) {
            $this->post(route('studio.orders.request-revision', $this->order), [
                'reason' => "Revision request {$i+1}",
            ]);
        }

        $this->order->refresh();
        $this->assertEquals(3, $this->order->revision_count);
    }

    /** @test */
    public function buyer_can_view_revision_history()
    {
        $revisions = WorkRevision::factory(3)->create([
            'service_order_id' => $this->order->id,
        ]);

        $this->actingAs($this->buyer);

        $response = $this->get(route('studio.orders.revision-history', $this->order));

        $response->assertStatus(200);
        foreach ($revisions as $revision) {
            $response->assertSeeText($revision->request_reason);
        }
    }

    /** @test */
    public function only_involved_parties_can_access_revision()
    {
        $revision = WorkRevision::factory()->create([
            'service_order_id' => $this->order->id,
        ]);

        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $response = $this->get(route('studio.orders.revision-history', $this->order));

        $response->assertStatus(403);
    }

    /** @test */
    public function revision_status_labels_are_correct()
    {
        $revision = WorkRevision::factory()->create(['status' => 'pending']);
        $this->assertEquals('Pending', $revision->getStatusLabel());

        $revision->update(['status' => 'submitted']);
        $this->assertEquals('Submitted', $revision->getStatusLabel());

        $revision->update(['status' => 'accepted']);
        $this->assertEquals('Accepted', $revision->getStatusLabel());

        $revision->update(['status' => 'rejected']);
        $this->assertEquals('Rejected', $revision->getStatusLabel());
    }
}
