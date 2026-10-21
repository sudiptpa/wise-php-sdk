<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources\UserTokens;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sujip\Wise\Resources\UserTokens\Models\UserToken;

final class UserTokenTest extends TestCase
{
    public function test_expires_at_prefers_value_returned_by_wise(): void
    {
        $token = UserToken::fromArray(['expires_in' => 60, 'expires_at' => '2025-04-11T03:43:28Z']);

        self::assertSame(
            '2025-04-11T03:43:28+00:00',
            $token->expiresAt(new DateTimeImmutable('2030-01-01T00:00:00Z'))?->format(DATE_ATOM),
        );
    }

    public function test_expires_at_is_computed_from_expires_in_when_absent(): void
    {
        $token = UserToken::fromArray(['expires_in' => 43199]);

        self::assertNull($token->expiresAt);
        self::assertSame(
            '2025-01-01T11:59:59+00:00',
            $token->expiresAt(new DateTimeImmutable('2025-01-01T00:00:00Z'))?->format(DATE_ATOM),
        );
    }

    public function test_expires_at_is_null_without_expiry_fields(): void
    {
        $token = UserToken::fromArray(['access_token' => 'at']);

        self::assertNull($token->expiresAt());
        self::assertNull($token->refreshTokenExpiresIn);
        self::assertNull($token->refreshTokenExpiresAt);
        self::assertNull($token->scope);
        self::assertNull($token->createdAt);
    }
}
