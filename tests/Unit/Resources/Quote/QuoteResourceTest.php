<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources\Quote;

use PHPUnit\Framework\TestCase;
use Sujip\Wise\Resources\Quote\Requests\CreateAuthenticatedQuoteRequest;
use Sujip\Wise\Resources\Quote\Requests\UpdateQuoteRequest;
use Sujip\Wise\Tests\Support\FakeTransport;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Tests\Support\TestClientFactory;

final class QuoteResourceTest extends TestCase
{
    public function testCreatesAuthenticatedQuoteAndBuildsRequest(): void
    {
        $fixture = file_get_contents(__DIR__ . '/../../../Fixtures/wise/quote.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $quote = $client->quote()->createAuthenticated(123, CreateAuthenticatedQuoteRequest::fixedTarget('USD', 'EUR', 92.5));

        self::assertSame('101', $quote->id);
        self::assertSame('POST', $transport->lastRequest()->getMethod());
        self::assertSame('/v3/profiles/123/quotes', $transport->lastRequest()->getUri()->getPath());
        self::assertSame('Bearer test-token', $transport->lastRequest()->getHeaderLine('Authorization'));
    }

    public function testCreatesUnauthenticatedQuoteWithoutAuthorizationHeader(): void
    {
        $fixture = file_get_contents(__DIR__ . '/../../../Fixtures/wise/quote.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $client->quote()->createUnauthenticated(
            new \Sujip\Wise\Resources\Quote\Requests\CreateUnauthenticatedQuoteRequest('USD', 'EUR', 100.0),
        );

        self::assertSame('/v3/quotes', $transport->lastRequest()->getUri()->getPath());
        self::assertFalse($transport->lastRequest()->hasHeader('Authorization'));
    }

    public function testUpdatesQuote(): void
    {
        $fixture = file_get_contents(__DIR__ . '/../../../Fixtures/wise/quote.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $client->quote()->update(123, 101, new UpdateQuoteRequest(targetAmount: 90.0));

        self::assertSame('PATCH', $transport->lastRequest()->getMethod());
        self::assertStringContainsString('targetAmount', (string) $transport->lastRequest()->getBody());
    }
}
