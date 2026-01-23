<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SfuAndRtcTest extends TestCase
{
    public function testIceEndpointReturnsServers(): void
    {
        $res = $this->get('/api/rtc/ice');
        $res->assertStatus(200)->assertJsonStructure(['iceServers']);
    }

    public function testSfuConfigReturnsProvider(): void
    {
        config()->set('sfu.provider', 'twilio');
        $res = $this->get('/api/rtc/sfu/config');
        $res->assertStatus(200)->assertJson(['provider' => 'twilio']);
    }

    public function testLogsIndexReturnsList(): void
    {
        $res = $this->get('/api/logs');
        $res->assertStatus(200)->assertJsonStructure(['logs']);
    }
}
