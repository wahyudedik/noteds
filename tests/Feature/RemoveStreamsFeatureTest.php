<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class RemoveStreamsFeatureTest extends TestCase
{
    public function test_streams_routes_return_410()
    {
        $this->withoutMiddleware();
        $this->get('/streams')->assertStatus(410);
        $this->getJson('/streams/any')->assertStatus(410);
        $this->postJson('/streams')->assertStatus(410);
        $this->postJson('/streams/any/start')->assertStatus(410);
        $this->postJson('/streams/any/end')->assertStatus(410);
        $this->postJson('/api/streams/any/chat')->assertStatus(410);
    }
}
