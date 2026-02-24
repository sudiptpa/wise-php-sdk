<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources\Balance;

use PHPUnit\Framework\TestCase;
use Sujip\Wise\Resources\Balance\Requests\AddExcessMoneyAccountRequest;
use Sujip\Wise\Resources\Balance\Requests\CreateBalanceMovementRequest;
use Sujip\Wise\Resources\Balance\Requests\CreateBalanceRequest;
use Sujip\Wise\Tests\Support\FakeTransport;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Tests\Support\TestClientFactory;

final class BalanceResourceTest extends TestCase
{
    public function test_create_list_get_and_close_balance(): void
    {
        $balance = file_get_contents(__DIR__.'/../../../Fixtures/wise/balance.json');
        $list = file_get_contents(__DIR__.'/../../../Fixtures/wise/balance_list.json');

        $transport = new FakeTransport([
            Psr7Factory::response(200, (string) $balance),
            Psr7Factory::response(200, (string) $list),
            Psr7Factory::response(200, (string) $balance),
            Psr7Factory::response(200, (string) $balance),
        ]);
        $client = TestClientFactory::make($transport);

        $created = $client->balance()->create(123, new CreateBalanceRequest(['currency' => 'EUR']));
        self::assertSame(501, $created->id);
        self::assertSame('/v4/profiles/123/balances', $transport->requests()[0]->getUri()->getPath());

        $balances = $client->balance()->list(123, 'STANDARD');
        self::assertCount(2, $balances->all());
        self::assertStringContainsString('types=STANDARD', $transport->requests()[1]->getUri()->getQuery());

        $single = $client->balance()->get(123, 501);
        self::assertSame(501, $single->id);
        self::assertSame('/v4/profiles/123/balances/501', $transport->requests()[2]->getUri()->getPath());

        $closed = $client->balance()->close(123, 501);
        self::assertSame(501, $closed->id);
        self::assertSame('DELETE', $transport->requests()[3]->getMethod());
    }

    public function test_balance_related_operations(): void
    {
        $movement = file_get_contents(__DIR__.'/../../../Fixtures/wise/balance_movement.json');
        $capacity = file_get_contents(__DIR__.'/../../../Fixtures/wise/balance_capacity.json');
        $excess = file_get_contents(__DIR__.'/../../../Fixtures/wise/excess_money_account.json');
        $totalFunds = file_get_contents(__DIR__.'/../../../Fixtures/wise/total_funds.json');

        $transport = new FakeTransport([
            Psr7Factory::response(200, (string) $movement),
            Psr7Factory::response(200, (string) $capacity),
            Psr7Factory::response(200, (string) $excess),
            Psr7Factory::response(200, (string) $totalFunds),
        ]);
        $client = TestClientFactory::make($transport);

        $resultMovement = $client->balance()->move(123, new CreateBalanceMovementRequest([
            'sourceBalanceId' => 501,
            'targetBalanceId' => 502,
            'amount' => ['value' => 5, 'currency' => 'EUR'],
        ]));
        self::assertSame('bm_1001', $resultMovement->id);
        self::assertSame('/v2/profiles/123/balance-movements', $transport->requests()[0]->getUri()->getPath());

        $resultCapacity = $client->balance()->capacity(123);
        self::assertTrue($resultCapacity->supports('EUR'));
        self::assertSame('/v1/profiles/123/balance-capacity', $transport->requests()[1]->getUri()->getPath());

        $resultExcess = $client->balance()->addExcessMoneyAccount(123, new AddExcessMoneyAccountRequest(['accountId' => 2001]));
        self::assertSame(2001, $resultExcess->accountId);
        self::assertSame('/v1/profiles/123/excess-money-account', $transport->requests()[2]->getUri()->getPath());

        $resultTotalFunds = $client->balance()->totalFunds(123, 'usd');
        self::assertSame(1000.0, $resultTotalFunds->totalWorth->value);
        self::assertSame('USD', $resultTotalFunds->totalWorth->currency);
        self::assertSame('/v1/profiles/123/total-funds/USD', $transport->requests()[3]->getUri()->getPath());
    }
}
