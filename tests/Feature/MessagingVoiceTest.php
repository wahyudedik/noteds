<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Conversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MessagingVoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_voice_rejects_long_duration(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $conv = Conversation::factory()->create();
        $conv->participants()->create(['user_id' => $user->id, 'is_active' => true]);
        $this->actingAs($user);
        $file = UploadedFile::fake()->create('voice.webm', 100, 'audio/webm');
        $res = $this->post(route('messaging.messages.voice.store', $conv->id), [
            'voice' => $file,
            'duration' => 121,
        ]);
        $res->assertStatus(302); // validation fails
    }

    public function test_upload_voice_stores_waveform(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $conv = Conversation::factory()->create();
        $conv->participants()->create(['user_id' => $user->id, 'is_active' => true]);
        $this->actingAs($user);
        $file = UploadedFile::fake()->create('voice.webm', 100, 'audio/webm');
        $waveform = array_fill(0, 10, 0.5);
        $res = $this->post(route('messaging.messages.voice.store', $conv->id), [
            'voice' => $file,
            'duration' => 10,
            'waveform' => json_encode($waveform),
        ]);
        $res->assertStatus(201);
        $msg = $res->json('message');
        $this->assertNotEmpty($msg['media']);
        $this->assertEquals($waveform, $msg['media'][0]['waveform']);
    }
}
