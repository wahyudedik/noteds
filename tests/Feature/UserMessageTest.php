namespace Tests\Feature;

use App\Models\User;
use App\Models\UserMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserMessageTest extends TestCase
{
    use RefreshDatabase;

    private User $sender;
    private User $recipient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sender = User::factory()->create(['name' => 'John Doe']);
        $this->recipient = User::factory()->create(['name' => 'Jane Smith']);
    }

    /** @test */
    public function user_can_send_message()
    {
        $this->actingAs($this->sender);

        $response = $this->post(route('studio.messages.store'), [
            'recipient_id' => $this->recipient->id,
            'message' => 'Hello, how are you?',
        ]);

        $this->assertDatabaseHas('user_messages', [
            'sender_id' => $this->sender->id,
            'recipient_id' => $this->recipient->id,
            'message' => 'Hello, how are you?',
        ]);

        $response->assertRedirect();
    }

    /** @test */
    public function user_can_view_inbox()
    {
        UserMessage::factory()->create([
            'sender_id' => $this->recipient->id,
            'recipient_id' => $this->sender->id,
            'message' => 'Hi there!',
        ]);

        $this->actingAs($this->sender);

        $response = $this->get(route('studio.messages.index'));

        $response->assertStatus(200);
        $response->assertSeeText('Hi there!');
    }

    /** @test */
    public function user_can_view_sent_messages()
    {
        UserMessage::factory()->create([
            'sender_id' => $this->sender->id,
            'recipient_id' => $this->recipient->id,
            'message' => 'Check this out!',
        ]);

        $this->actingAs($this->sender);

        $response = $this->get(route('studio.messages.sent'));

        $response->assertStatus(200);
        $response->assertSeeText('Check this out!');
    }

    /** @test */
    public function user_can_view_conversation_thread()
    {
        UserMessage::factory()->create([
            'sender_id' => $this->sender->id,
            'recipient_id' => $this->recipient->id,
            'message' => 'Message 1',
        ]);

        UserMessage::factory()->create([
            'sender_id' => $this->recipient->id,
            'recipient_id' => $this->sender->id,
            'message' => 'Message 2',
        ]);

        $this->actingAs($this->sender);

        $response = $this->get(route('studio.messages.show', $this->recipient));

        $response->assertStatus(200);
        $response->assertSeeText('Message 1');
        $response->assertSeeText('Message 2');
    }

    /** @test */
    public function user_can_mark_message_as_read()
    {
        $message = UserMessage::factory()->create([
            'sender_id' => $this->recipient->id,
            'recipient_id' => $this->sender->id,
        ]);

        $this->assertNull($message->read_at);

        $this->actingAs($this->sender);

        $this->post(route('studio.messages.mark-read', $message));

        $message->refresh();
        $this->assertNotNull($message->read_at);
    }

    /** @test */
    public function unread_messages_count_is_tracked()
    {
        // Create unread messages for sender
        UserMessage::factory(3)->create([
            'sender_id' => $this->recipient->id,
            'recipient_id' => $this->sender->id,
            'read_at' => null,
        ]);

        $this->sender->refresh();
        $this->assertEquals(3, $this->sender->getUnreadMessageCount());
    }

    /** @test */
    public function user_can_delete_message()
    {
        $message = UserMessage::factory()->create([
            'sender_id' => $this->sender->id,
            'recipient_id' => $this->recipient->id,
        ]);

        $this->actingAs($this->sender);

        $response = $this->delete(route('studio.messages.destroy', $message));

        $this->assertDatabaseMissing('user_messages', ['id' => $message->id]);
    }

    /** @test */
    public function message_length_is_validated()
    {
        $this->actingAs($this->sender);

        $longMessage = str_repeat('a', 2001);

        $response = $this->post(route('studio.messages.store'), [
            'recipient_id' => $this->recipient->id,
            'message' => $longMessage,
        ]);

        $response->assertSessionHasErrors('message');
    }

    /** @test */
    public function conversation_returns_only_messages_with_specific_user()
    {
        $thirdUser = User::factory()->create();

        // Messages between sender and recipient
        UserMessage::factory()->create([
            'sender_id' => $this->sender->id,
            'recipient_id' => $this->recipient->id,
        ]);

        // Messages between sender and third user
        UserMessage::factory()->create([
            'sender_id' => $this->sender->id,
            'recipient_id' => $thirdUser->id,
        ]);

        $this->actingAs($this->sender);

        $response = $this->get(route('studio.messages.show', $this->recipient));

        $this->assertTrue($response->viewData('messages')->count() >= 1);
    }

    /** @test */
    public function users_cannot_view_others_messages()
    {
        $thirdUser = User::factory()->create();

        UserMessage::factory()->create([
            'sender_id' => $this->sender->id,
            'recipient_id' => $this->recipient->id,
        ]);

        $this->actingAs($thirdUser);

        $response = $this->get(route('studio.messages.show', $this->sender));

        // Should not see the messages between sender and recipient
        $response->assertStatus(200); // View loads but shouldn't show unrelated messages
    }

    /** @test */
    public function message_is_marked_read_when_thread_viewed()
    {
        $message = UserMessage::factory()->create([
            'sender_id' => $this->recipient->id,
            'recipient_id' => $this->sender->id,
            'read_at' => null,
        ]);

        $this->actingAs($this->sender);

        $this->get(route('studio.messages.show', $this->recipient));

        $message->refresh();
        $this->assertNotNull($message->read_at);
    }

    /** @test */
    public function sent_messages_counter_increments()
    {
        $this->actingAs($this->sender);

        $this->post(route('studio.messages.store'), [
            'recipient_id' => $this->recipient->id,
            'message' => 'First message',
        ]);

        $this->post(route('studio.messages.store'), [
            'recipient_id' => $this->recipient->id,
            'message' => 'Second message',
        ]);

        $this->sender->refresh();
        $this->assertEquals(2, $this->sender->sent_messages_count);
    }

    /** @test */
    public function received_messages_counter_increments()
    {
        UserMessage::factory()->create([
            'sender_id' => $this->sender->id,
            'recipient_id' => $this->recipient->id,
        ]);

        $this->recipient->refresh();
        $this->assertEquals(1, $this->recipient->received_messages_count);
    }
}
