<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources\Address;

use PHPUnit\Framework\TestCase;
use Sujip\Wise\Resources\Address\Requests\CreateAddressRequest;
use Sujip\Wise\Resources\Address\Requests\ResolveAddressRequirementsRequest;
use Sujip\Wise\Tests\Support\FakeTransport;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Tests\Support\TestClientFactory;

final class AddressResourceTest extends TestCase
{
    public function test_create_list_and_get_address(): void
    {
        $single = file_get_contents(__DIR__.'/../../../Fixtures/wise/address.json');
        $list = file_get_contents(__DIR__.'/../../../Fixtures/wise/address_list.json');

        $transport = new FakeTransport([
            Psr7Factory::response(200, (string) $single),
            Psr7Factory::response(200, (string) $list),
            Psr7Factory::response(200, (string) $single),
        ]);
        $client = TestClientFactory::make($transport);

        $created = $client->address()->create(new CreateAddressRequest(['profileId' => 123, 'country' => 'US']));
        self::assertSame(7001, $created->id);
        self::assertSame('/v1/addresses', $transport->requests()[0]->getUri()->getPath());

        $addresses = $client->address()->list(123);
        self::assertCount(1, $addresses->all());
        self::assertStringContainsString('profileId=123', $transport->requests()[1]->getUri()->getQuery());

        $address = $client->address()->get(7001);
        self::assertSame(7001, $address->id);
        self::assertSame('/v1/addresses/7001', $transport->requests()[2]->getUri()->getPath());
    }

    public function test_fetches_address_requirements_with_get_and_post(): void
    {
        $requirements = file_get_contents(__DIR__.'/../../../Fixtures/wise/address_requirements.json');

        $transport = new FakeTransport([
            Psr7Factory::response(200, (string) $requirements),
            Psr7Factory::response(200, (string) $requirements),
        ]);
        $client = TestClientFactory::make($transport);

        $generic = $client->address()->requirements();
        self::assertCount(2, $generic->all());
        self::assertSame('GET', $transport->requests()[0]->getMethod());
        self::assertSame('/v1/address-requirements', $transport->requests()[0]->getUri()->getPath());

        $resolved = $client->address()->requirements(new ResolveAddressRequirementsRequest([
            'country' => 'US',
            'type' => 'PRIVATE',
        ]));
        self::assertCount(2, $resolved->all());
        self::assertSame('POST', $transport->requests()[1]->getMethod());
        self::assertSame('/v1/address-requirements', $transport->requests()[1]->getUri()->getPath());
    }
}
