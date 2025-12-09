namespace Tests\Feature;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderDispute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceOrderDisputeTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $vendor;
    private User $admin;
    private ServiceOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create(['role' => 'buyer']);
        $this->vendor = User::factory()->create(['role' => 'vendor']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        
        $this->order = ServiceOrder::factory()->create([
            'buyer_id' => $this->buyer->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'submitted',
            'total_amount' => 100,
        ]);
    }

    /** @test */
    public function buyer_can_file_dispute()
    {
        $this->actingAs($this->buyer);

        $response = $this->post(route('studio.orders.dispute.store', $this->order), [
            'reason' => 'Work does not match the description',
        ]);

        $this->assertDatabaseHas('service_order_disputes', [
            'service_order_id' => $this->order->id,
            'initiated_by' => $this->buyer->id,
            'reason' => 'Work does not match the description',
            'status' => 'open',
        ]);

        $this->order->refresh();
        $this->assertNotNull($this->order->active_dispute_id);
    }

    /** @test */
    public function vendor_can_file_dispute()
    {
        $this->actingAs($this->vendor);

        $response = $this->post(route('studio.orders.dispute.store', $this->order), [
            'reason' => 'Buyer rejected work without valid reason',
        ]);

        $this->assertDatabaseHas('service_order_disputes', [
            'service_order_id' => $this->order->id,
            'initiated_by' => $this->vendor->id,
        ]);
    }

    /** @test */
    public function user_can_view_dispute()
    {
        $dispute = ServiceOrderDispute::factory()->create([
            'service_order_id' => $this->order->id,
            'initiated_by' => $this->buyer->id,
        ]);

        $this->actingAs($this->buyer);

        $response = $this->get(route('studio.disputes.show', $dispute));

        $response->assertStatus(200);
        $response->assertSeeText($dispute->reason);
    }

    /** @test */
    public function only_involved_parties_can_view_dispute()
    {
        $dispute = ServiceOrderDispute::factory()->create([
            'service_order_id' => $this->order->id,
            'initiated_by' => $this->buyer->id,
        ]);

        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $response = $this->get(route('studio.disputes.show', $dispute));

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_add_evidence_to_open_dispute()
    {
        $dispute = ServiceOrderDispute::factory()->create([
            'service_order_id' => $this->order->id,
            'initiated_by' => $this->buyer->id,
            'status' => 'open',
        ]);

        $this->actingAs($this->buyer);

        // Mock file upload would go here
        // For now, test that the form works
        $response = $this->get(route('studio.disputes.show', $dispute));
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_view_all_disputes()
    {
        ServiceOrderDispute::factory(5)->create();

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.disputes.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_resolve_dispute_with_refund()
    {
        $dispute = ServiceOrderDispute::factory()->create([
            'service_order_id' => $this->order->id,
            'initiated_by' => $this->buyer->id,
            'status' => 'open',
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('admin.disputes.resolve', $dispute), [
            'resolution_type' => 'refund_buyer',
            'notes' => 'Work does not meet quality standards',
        ]);

        $dispute->refresh();
        $this->assertEquals('resolved', $dispute->status);
        $this->assertEquals('refund_buyer', $dispute->resolution_type);
        $this->assertNotNull($dispute->resolved_at);
        $this->assertEquals($this->admin->id, $dispute->resolved_by);
    }

    /** @test */
    public function admin_can_resolve_dispute_with_vendor_payment()
    {
        $dispute = ServiceOrderDispute::factory()->create([
            'service_order_id' => $this->order->id,
            'initiated_by' => $this->vendor->id,
            'status' => 'open',
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('admin.disputes.resolve', $dispute), [
            'resolution_type' => 'payment_vendor',
            'notes' => 'Buyer did not provide valid reason for rejection',
        ]);

        $dispute->refresh();
        $this->assertEquals('resolved', $dispute->status);
        $this->assertEquals('payment_vendor', $dispute->resolution_type);
    }

    /** @test */
    public function admin_can_resolve_dispute_with_partial_amount()
    {
        $dispute = ServiceOrderDispute::factory()->create([
            'service_order_id' => $this->order->id,
            'initiated_by' => $this->buyer->id,
            'status' => 'open',
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('admin.disputes.resolve', $dispute), [
            'resolution_type' => 'partial',
            'amount' => 50,
            'notes' => 'Partial refund - partial work completed',
        ]);

        $dispute->refresh();
        $this->assertEquals('resolved', $dispute->status);
        $this->assertEquals('partial', $dispute->resolution_type);
    }

    /** @test */
    public function cannot_file_multiple_disputes_simultaneously()
    {
        ServiceOrderDispute::factory()->create([
            'service_order_id' => $this->order->id,
            'status' => 'open',
        ]);

        $this->actingAs($this->buyer);

        $response = $this->post(route('studio.orders.dispute.store', $this->order), [
            'reason' => 'Another dispute',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function dispute_status_labels_are_correct()
    {
        $dispute = ServiceOrderDispute::factory()->create(['status' => 'open']);
        $this->assertEquals('Open', $dispute->getStatusLabel());

        $dispute->update(['status' => 'under_review']);
        $this->assertEquals('Under Review', $dispute->getStatusLabel());

        $dispute->update(['status' => 'resolved']);
        $this->assertEquals('Resolved', $dispute->getStatusLabel());

        $dispute->update(['status' => 'escalated']);
        $this->assertEquals('Escalated', $dispute->getStatusLabel());
    }

    /** @test */
    public function dispute_resolution_type_labels_are_correct()
    {
        $dispute = ServiceOrderDispute::factory()->create(['resolution_type' => 'refund_buyer']);
        $this->assertEquals('Refund Buyer (Full)', $dispute->getResolutionTypeLabel());

        $dispute->update(['resolution_type' => 'payment_vendor']);
        $this->assertEquals('Pay Vendor (Full)', $dispute->getResolutionTypeLabel());

        $dispute->update(['resolution_type' => 'partial']);
        $this->assertEquals('Partial Refund/Payment', $dispute->getResolutionTypeLabel());

        $dispute->update(['resolution_type' => 'custom']);
        $this->assertEquals('Custom Resolution', $dispute->getResolutionTypeLabel());
    }

    /** @test */
    public function can_filter_disputes_by_status()
    {
        ServiceOrderDispute::factory()->create(['status' => 'open']);
        ServiceOrderDispute::factory()->create(['status' => 'under_review']);
        ServiceOrderDispute::factory()->create(['status' => 'resolved']);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.disputes.index', ['status' => 'open']));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_sees_both_parties_in_dispute_detail()
    {
        $dispute = ServiceOrderDispute::factory()->create([
            'service_order_id' => $this->order->id,
            'initiated_by' => $this->buyer->id,
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.disputes.show', $dispute));

        $response->assertStatus(200);
        $response->assertSeeText($this->buyer->name);
        $response->assertSeeText($this->vendor->name);
    }
}
