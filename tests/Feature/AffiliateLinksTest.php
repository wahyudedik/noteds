<?php

use App\Models\User;
use App\Models\AffiliateLink;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles and permissions manually for tests
    $role = Role::firstOrCreate(['name' => 'affiliate']);
    $this->affiliate = User::factory()->create();
    $this->affiliate->assignRole($role);
});

test('user can create affiliate link', function () {
    $link = AffiliateLink::factory()->make();
    
    $response = $this->actingAs($this->affiliate)->post(route('affiliate.links.store'), [
        'name' => $link->name,
        'url' => 'https://example.com/product?ref=test',
    ]);

    $response->assertRedirect();
    expect($this->affiliate->affiliateLinks()->count())->toBeGreaterThan(0);
});

test('user can update affiliate link', function () {
    $link = AffiliateLink::factory()->create(['user_id' => $this->affiliate->id]);
    
    $response = $this->actingAs($this->affiliate)->put(
        route('affiliate.links.update', $link),
        ['name' => 'Updated Link Name']
    );

    $response->assertRedirect();
    expect($link->fresh()->name)->toBe('Updated Link Name');
});

test('user can delete affiliate link', function () {
    $link = AffiliateLink::factory()->create(['user_id' => $this->affiliate->id]);
    $id = $link->id;
    
    $response = $this->actingAs($this->affiliate)->delete(
        route('affiliate.links.delete', $link)
    );

    $response->assertRedirect();
    expect(AffiliateLink::find($id))->toBeNull();
});

test('user can only manage own links', function () {
    $otherUser = User::factory()->create();
    $otherLink = AffiliateLink::factory()->create(['user_id' => $otherUser->id]);
    
    $response = $this->actingAs($this->affiliate)->put(
        route('affiliate.links.update', $otherLink),
        ['name' => 'Hacked Name']
    );

    $response->assertForbidden();
    expect($otherLink->fresh()->name)->not()->toBe('Hacked Name');
});

test('affiliate link slug is auto generated', function () {
    $link = AffiliateLink::factory()->create(['user_id' => $this->affiliate->id]);
    
    expect($link->unique_slug)->not()->toBeNull();
});
