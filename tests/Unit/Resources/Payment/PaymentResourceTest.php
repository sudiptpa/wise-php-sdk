<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources\Payment;

use PHPUnit\Framework\TestCase;
use Sujip\Wise\Resources\Payment\Enums\PaymentType;
use Sujip\Wise\Resources\Payment\Requests\FundTransferRequest;
use Sujip\Wise\Tests\Support\FakeTransport;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Tests\Support\TestClientFactory;

final class PaymentResourceTest extends TestCase
{
    public function test_funds_transfer(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/payment.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $payment = $client->payment()->fundTransfer(123, 3001, new FundTransferRequest(PaymentType::Balance));

        self::assertSame(3001, $payment->transferId);
        self::assertSame(PaymentType::Balance, $payment->options[0]->typeEnum());
        self::assertSame('/v3/profiles/123/transfers/3001/payments', $transport->lastRequest()->getUri()->getPath());
    }
}
