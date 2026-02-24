<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources\BankAccountDetails;

use PHPUnit\Framework\TestCase;
use Sujip\Wise\Resources\BankAccountDetails\Requests\CreateBankDetailsRequest;
use Sujip\Wise\Resources\BankAccountDetails\Requests\CreateDetailsOrderRequest;
use Sujip\Wise\Resources\BankAccountDetails\Requests\MarkPaymentReturnRequest;
use Sujip\Wise\Tests\Support\FakeTransport;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Tests\Support\TestClientFactory;

final class BankAccountDetailsResourceTest extends TestCase
{
    public function test_bank_account_details_operations(): void
    {
        $order = file_get_contents(__DIR__.'/../../../Fixtures/wise/bank_account_details_order.json');
        $detail = file_get_contents(__DIR__.'/../../../Fixtures/wise/bank_account_detail.json');
        $detailsList = file_get_contents(__DIR__.'/../../../Fixtures/wise/bank_account_details_list.json');
        $paymentReturn = file_get_contents(__DIR__.'/../../../Fixtures/wise/payment_return.json');

        $transport = new FakeTransport([
            Psr7Factory::response(200, (string) $order),
            Psr7Factory::response(200, (string) $detail),
            Psr7Factory::response(200, (string) $detailsList),
            Psr7Factory::response(200, '['.(string) $order.']'),
            Psr7Factory::response(200, (string) $paymentReturn),
        ]);
        $client = TestClientFactory::make($transport);

        $createdOrder = $client->bankAccountDetails()->createOrder(123, new CreateDetailsOrderRequest(['currency' => 'EUR']));
        self::assertSame(8801, $createdOrder->id);
        self::assertSame('/v1/profiles/123/account-details-orders', $transport->requests()[0]->getUri()->getPath());

        $createdDetails = $client->bankAccountDetails()->createBankDetails(123, new CreateBankDetailsRequest(['currency' => 'EUR']));
        self::assertSame(9901, $createdDetails->id);
        self::assertSame('/v3/profiles/123/bank-details', $transport->requests()[1]->getUri()->getPath());

        $details = $client->bankAccountDetails()->list(123);
        self::assertCount(1, $details->all());
        self::assertSame('/v1/profiles/123/account-details', $transport->requests()[2]->getUri()->getPath());

        $orders = $client->bankAccountDetails()->listOrders(123);
        self::assertCount(1, $orders->all());
        self::assertSame('/v3/profiles/123/account-details-orders', $transport->requests()[3]->getUri()->getPath());

        $returned = $client->bankAccountDetails()->markPaymentReturn(123, 654, new MarkPaymentReturnRequest(['reason' => 'TEST']));
        self::assertSame('returned', $returned->state);
        self::assertSame('/v1/profiles/123/account-details/payments/654/returns', $transport->requests()[4]->getUri()->getPath());
    }
}
