<?php

declare(strict_types=1);

namespace Theocode\Wise\Tests\Unit\Resources\BalanceStatement;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Theocode\Wise\Resources\BalanceStatement\Requests\GetBalanceStatementRequest;
use Theocode\Wise\Tests\Support\FakeTransport;
use Theocode\Wise\Tests\Support\Psr7Factory;
use Theocode\Wise\Tests\Support\TestClientFactory;

final class BalanceStatementResourceTest extends TestCase
{
    public function test_gets_balance_statement_json(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/balance_statement.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $statement = $client->balanceStatement()->getJson(123, 501, new GetBalanceStatementRequest(
            intervalStart: new DateTimeImmutable('2026-01-01T00:00:00Z'),
            intervalEnd: new DateTimeImmutable('2026-01-31T23:59:59Z'),
            type: 'COMPACT',
        ));

        self::assertCount(2, $statement->transactions);
        self::assertSame('/v1/profiles/123/balance-statements/501/statement.json', $transport->lastRequest()->getUri()->getPath());
        self::assertStringContainsString('type=COMPACT', $transport->lastRequest()->getUri()->getQuery());
    }
}
