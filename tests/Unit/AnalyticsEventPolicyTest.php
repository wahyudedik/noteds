<?php

namespace Tests\Unit;

use App\Models\AnalyticsEvent;
use App\Models\User;
use App\Policies\AnalyticsEventPolicy;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalyticsEventPolicyTest extends TestCase
{
    public function test_policy_permissions_for_roles()
    {
        $policy = new AnalyticsEventPolicy();
        $event = new AnalyticsEvent([
            'id' => (string) Str::uuid(),
            'user_id' => (string) Str::uuid(),
            'type' => 'too_many_requests',
            'payload' => [],
        ]);
        $admin = new User(['role' => 'admin']);
        $analyst = new User(['role' => 'analyst']);
        $viewer = new User(['role' => 'viewer']);
        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->viewAny($analyst));
        $this->assertFalse($policy->viewAny($viewer));
        $this->assertTrue($policy->view($viewer, $event));
        $this->assertTrue($policy->create($admin));
        $this->assertTrue($policy->create($analyst));
        $this->assertFalse($policy->create($viewer));
        $this->assertTrue($policy->update($admin, $event));
        $this->assertFalse($policy->update($analyst, $event));
        $this->assertFalse($policy->update($viewer, $event));
        $this->assertTrue($policy->export($admin));
        $this->assertTrue($policy->export($analyst));
        $this->assertFalse($policy->export($viewer));
    }
}
