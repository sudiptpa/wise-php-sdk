<?php

declare(strict_types=1);

namespace Theocode\Wise\Auth;

use Closure;
use Theocode\Wise\Contracts\AccessTokenProviderInterface;
use Theocode\Wise\Exceptions\ValidationException;
use Theocode\Wise\Exceptions\WiseException;
use Theocode\Wise\Resources\UserTokens\Requests\CreateUserTokenRequest;
use Theocode\Wise\WiseClient;

/**
 * Fetches a client credentials token from `POST /oauth/token` and caches it in memory until it is
 * within `$expiryMarginSeconds` of expiring. The cache lives only as long as this object; persist
 * tokens across processes in your application or framework if you need to.
 *
 * `$tokenClient` only sends the unauthenticated token request, so it can be a client built without
 * an access token provider.
 */
final class ClientCredentialsTokenProvider implements AccessTokenProviderInterface
{
    private readonly CreateUserTokenRequest $request;

    /** @var (Closure(): int)|null */
    private ?Closure $now;

    private ?string $accessToken = null;

    private ?int $expiresAt = null;

    /**
     * @param  (callable(): int)|null  $now  Returns the current Unix timestamp; defaults to time().
     */
    public function __construct(
        private readonly WiseClient $tokenClient,
        string $clientId,
        string $clientSecret,
        private readonly int $expiryMarginSeconds = 60,
        ?callable $now = null,
    ) {
        if ($this->expiryMarginSeconds < 0) {
            throw new ValidationException('Token expiry margin cannot be negative.');
        }

        $this->request = CreateUserTokenRequest::clientCredentials($clientId, $clientSecret);
        $this->now = $now === null ? null : Closure::fromCallable($now);
    }

    public function getAccessToken(): string
    {
        $now = $this->now === null ? time() : ($this->now)();

        if ($this->accessToken !== null && $this->expiresAt !== null && $now < $this->expiresAt - $this->expiryMarginSeconds) {
            return $this->accessToken;
        }

        $token = $this->tokenClient->userTokens()->create($this->request);
        if ($token->accessToken === null || $token->accessToken === '') {
            throw new WiseException('Wise token response did not include an access_token.');
        }

        $this->accessToken = $token->accessToken;
        $this->expiresAt = $token->expiresIn !== null
            ? $now + $token->expiresIn
            : $token->expiresAt?->getTimestamp();

        return $this->accessToken;
    }
}
