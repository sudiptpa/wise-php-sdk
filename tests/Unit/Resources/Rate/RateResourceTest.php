<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources\Rate;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sujip\Wise\Resources\Rate\Requests\ListRatesRequest;
use Sujip\Wise\Tests\Support\FakeTransport;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Tests\Support\TestClientFactory;

final class RateResourceTest extends TestCase
{
    public function test_lists_rates_and_builds_query(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/rates.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $rates = $client->rate()->list(new ListRatesRequest(
            source: 'usd',
            target: 'eur',
            time: new DateTimeImmutable('2026-02-24T10:00:00Z'),
            group: 'hour',
        ));

        self::assertCount(2, $rates->all());
        self::assertSame('USD', $rates->all()[0]->source);
        self::assertSame('EUR', $rates->all()[0]->target);

        $query = $transport->lastRequest()->getUri()->getQuery();
        self::assertStringContainsString('source=USD', $query);
        self::assertStringContainsString('target=EUR', $query);
        self::assertStringContainsString('group=hour', $query);
    }
}
