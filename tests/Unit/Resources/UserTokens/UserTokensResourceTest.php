<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources\UserTokens;

use PHPUnit\Framework\TestCase;
use Sujip\Wise\Resources\UserTokens\Requests\CreateUserTokenRequest;
use Sujip\Wise\Tests\Support\FakeTransport;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Tests\Support\TestClientFactory;

final class UserTokensResourceTest extends TestCase
{
    public function test_creates_user_token_with_form_payload_and_no_auth_header(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/user_token.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $token = $client->userTokens()->create(CreateUserTokenRequest::refreshToken('cid', 'sec', 'rt'));

        self::assertSame('at_123', $token->accessToken);
        self::assertSame('/oauth/token', $transport->lastRequest()->getUri()->getPath());
        self::assertSame('application/x-www-form-urlencoded', $transport->lastRequest()->getHeaderLine('Content-Type'));
        self::assertFalse($transport->lastRequest()->hasHeader('Authorization'));
        self::assertStringContainsString('grant_type=refresh_token', (string) $transport->lastRequest()->getBody());
    }
}
