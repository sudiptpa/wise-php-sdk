<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources\Currencies;

use PHPUnit\Framework\TestCase;
use Sujip\Wise\Tests\Support\FakeTransport;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Tests\Support\TestClientFactory;

final class CurrenciesResourceTest extends TestCase
{
    public function test_lists_currencies_with_locale_header(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/currencies.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $currencies = $client->currencies()->list('en-US');

        self::assertCount(2, $currencies->all());
        self::assertSame('USD', $currencies->all()[0]->code);
        self::assertSame('/v1/currencies', $transport->lastRequest()->getUri()->getPath());
        self::assertSame('en-US', $transport->lastRequest()->getHeaderLine('Accept-Language'));
    }
}
