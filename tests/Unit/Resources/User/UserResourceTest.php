<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources\User;

use PHPUnit\Framework\TestCase;
use Sujip\Wise\Resources\User\Requests\CreateRegistrationCodeRequest;
use Sujip\Wise\Resources\User\Requests\UpdateUserContactEmailRequest;
use Sujip\Wise\Resources\User\Requests\UserExistsRequest;
use Sujip\Wise\Tests\Support\FakeTransport;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Tests\Support\TestClientFactory;

final class UserResourceTest extends TestCase
{
    public function test_user_resource_operations(): void
    {
        $user = file_get_contents(__DIR__.'/../../../Fixtures/wise/user.json');
        $exists = file_get_contents(__DIR__.'/../../../Fixtures/wise/user_exists.json');
        $registrationCode = file_get_contents(__DIR__.'/../../../Fixtures/wise/registration_code.json');
        $contactEmail = file_get_contents(__DIR__.'/../../../Fixtures/wise/user_contact_email.json');

        $transport = new FakeTransport([
            Psr7Factory::response(200, (string) $user),
            Psr7Factory::response(200, (string) $user),
            Psr7Factory::response(200, (string) $exists),
            Psr7Factory::response(200, (string) $registrationCode),
            Psr7Factory::response(200, (string) $contactEmail),
            Psr7Factory::response(200, (string) $contactEmail),
        ]);
        $client = TestClientFactory::make($transport);

        $me = $client->user()->me();
        self::assertSame(77, $me->id);
        self::assertSame('/v1/me', $transport->requests()[0]->getUri()->getPath());

        $single = $client->user()->get(77);
        self::assertSame(77, $single->id);
        self::assertSame('/v1/users/77', $transport->requests()[1]->getUri()->getPath());

        $existsResult = $client->user()->exists(new UserExistsRequest('jane@example.com'));
        self::assertTrue($existsResult->exists);
        self::assertSame('/v1/users/exists', $transport->requests()[2]->getUri()->getPath());

        $reg = $client->user()->createRegistrationCode(new CreateRegistrationCodeRequest('jane@example.com'));
        self::assertSame('reg_123', $reg->registrationCode);
        self::assertSame('/v1/user/signup/registration_code', $transport->requests()[3]->getUri()->getPath());

        $updated = $client->user()->updateContactEmail(77, new UpdateUserContactEmailRequest('new@example.com'));
        self::assertSame('new@example.com', $updated->email);
        self::assertSame('/v1/users/77/contact-email', $transport->requests()[4]->getUri()->getPath());

        $fetched = $client->user()->contactEmail(77);
        self::assertSame('new@example.com', $fetched->email);
        self::assertSame('/v1/users/77/contact-email', $transport->requests()[5]->getUri()->getPath());
    }
}
