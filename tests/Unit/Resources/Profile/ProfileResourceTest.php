<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources\Profile;

use PHPUnit\Framework\TestCase;
use Sujip\Wise\Tests\Support\FakeTransport;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Tests\Support\TestClientFactory;

final class ProfileResourceTest extends TestCase
{
    public function test_lists_profiles(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/profile_list.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $profiles = $client->profile()->list();

        self::assertCount(2, $profiles->all());
        self::assertSame('/v1/profiles', $transport->lastRequest()->getUri()->getPath());
    }
}
