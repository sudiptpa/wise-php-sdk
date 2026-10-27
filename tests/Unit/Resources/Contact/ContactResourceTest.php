<?php

declare(strict_types=1);

namespace Theocode\Wise\Tests\Unit\Resources\Contact;

use PHPUnit\Framework\TestCase;
use Theocode\Wise\Resources\Contact\Requests\CreateContactRequest;
use Theocode\Wise\Tests\Support\FakeTransport;
use Theocode\Wise\Tests\Support\Psr7Factory;
use Theocode\Wise\Tests\Support\TestClientFactory;

final class ContactResourceTest extends TestCase
{
    public function test_creates_contact_from_direct_identifier(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/contact.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $contact = $client->contact()->create(123, new CreateContactRequest('eur', 'john@example.com'));

        self::assertSame(9001, $contact->id);
        self::assertSame('/v2/profiles/123/contacts', $transport->lastRequest()->getUri()->getPath());
        self::assertStringContainsString('isDirectIdentifierCreation=true', $transport->lastRequest()->getUri()->getQuery());
    }
}
