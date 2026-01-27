<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TestableMediaStackService extends \App\Services\MediaStackService
{
    public function callIsApiKeyValid(): bool
    {
        return $this->isApiKeyValid();
    }
    public function callSanitizeCategories($c): array
    {
        return $this->sanitizeCategories($c);
    }
    public function callPerformRequest(array $p): ?\Illuminate\Http\Client\Response
    {
        return $this->performRequest($p);
    }
}

class MediaStackServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('mediastack.verify_ssl', false);
        Config::set('mediastack.retry_times', 3);
        Config::set('mediastack.retry_sleep_ms', 1);
        Config::set('mediastack.allowed_categories', ['business', 'technology', 'sports', 'health', 'science', 'entertainment', 'general', 'other']);
        Config::set('mediastack.default_categories', ['general', 'other']);
        Config::set('mediastack.api_endpoint', 'https://api.mediastack.com/v1/news');
        Cache::flush();
    }

    protected function makeService(string $apiKey): TestableMediaStackService
    {
        Config::set('mediastack.api_key', $apiKey);
        return new TestableMediaStackService();
    }

    public function testIsApiKeyValidAcceptsValid()
    {
        $key = str_repeat('A', 32) . '_VALID_KEY_' . str_repeat('Z', 10);
        $svc = $this->makeService($key);
        $this->assertTrue($svc->callIsApiKeyValid());
    }

    public function testIsApiKeyValidTooShort()
    {
        $svc = $this->makeService('SHORT_KEY_123');
        $this->assertFalse($svc->callIsApiKeyValid());
    }

    public function testIsApiKeyValidTooLong()
    {
        $svc = $this->makeService(str_repeat('X', 257));
        $this->assertFalse($svc->callIsApiKeyValid());
    }

    public function testIsApiKeyValidInvalidChars()
    {
        $svc = $this->makeService('INVALID-CHAR_KEY_' . str_repeat('A', 20));
        $this->assertFalse($svc->callIsApiKeyValid());
    }

    public function testIsApiKeyValidEmpty()
    {
        $svc = $this->makeService('');
        $this->assertFalse($svc->callIsApiKeyValid());
    }

    public function testSanitizeCategoriesArrayValid()
    {
        $svc = $this->makeService(str_repeat('A', 40));
        $out = $svc->callSanitizeCategories(['business', 'unknown', 'general']);
        $this->assertSame(['business', 'general'], $out);
    }

    public function testSanitizeCategoriesCommaString()
    {
        $svc = $this->makeService(str_repeat('A', 40));
        $out = $svc->callSanitizeCategories('sports, health,invalid');
        $this->assertSame(['sports', 'health'], $out);
    }

    public function testSanitizeCategoriesInvalidFallback()
    {
        $svc = $this->makeService(str_repeat('A', 40));
        $out = $svc->callSanitizeCategories(['invalid1', 'invalid2']);
        $this->assertSame(['general', 'other'], $out);
    }

    public function testPerformRequestSuccess()
    {
        $svc = $this->makeService(str_repeat('B', 40));
        Http::fake([
            'api.mediastack.com/*' => Http::response(['data' => [['url' => 'https://x', 'title' => 't']]], 200)
        ]);
        $resp = $svc->callPerformRequest(['languages' => 'en']);
        $this->assertNotNull($resp);
        $this->assertTrue($resp->successful());
    }

    public function testPerformRequestRetryAfter429ThenSuccess()
    {
        $svc = $this->makeService(str_repeat('C', 40));
        Http::fakeSequence()
            ->push(['error' => 'rate'], 429, ['Retry-After' => '1'])
            ->push(['data' => [['url' => 'https://x']]], 200);
        $resp = $svc->callPerformRequest(['languages' => 'en']);
        $this->assertNotNull($resp);
        $this->assertTrue($resp->successful());
    }

    public function testPerformRequestRetriesExhaustedThrows()
    {
        $this->expectException(\RuntimeException::class);
        $svc = $this->makeService(str_repeat('D', 40));
        Http::fakeSequence()
            ->push(['error' => 'server'], 500)
            ->push(['error' => 'server'], 503)
            ->push(['error' => 'server'], 504);
        $svc->callPerformRequest(['languages' => 'en']);
    }
}
