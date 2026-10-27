# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added
- `transfer()->list()` and `transfer()->iterate()` to list transfers (with profile, status, currency, date, limit, and offset filters) via `GET /v1/transfers`.
- `transfer()->cancel()` to cancel a transfer that is `incoming_payment_waiting` via `PUT /v1/transfers/{transferId}/cancel`.
- `Transfer` now hydrates the full Wise payload: `user`, `sourceAccount`, `reference` (prefers `details.reference`), `rate`, `created` (parsed as a UTC `DateTimeImmutable`), `business`, `hasActiveIssues`, `sourceCurrency`, `sourceValue`, `targetCurrency`, `targetValue`, `customerTransactionId`, `payinSessionId`, `details`, and `raw`.
- `ApiException::hasErrorCode()` to detect Wise `errors[].code` entries, e.g. `transfer.cancellation.not.allowed`.

## [0.2.0] - 2026-10-27

### Changed
- **Breaking:** the package is now published as `theocode12/wise-php-sdk`, a maintained fork of `sudiptpa/wise-php-sdk`, and the namespace changed from `Sujip\Wise` to `Theocode\Wise`. See [UPGRADE.md](UPGRADE.md).
- The default `ClientConfig::$userAgent` is now `theocode12/wise-php-sdk`.

### Added
- `WebhookSignatureVerifier` verifies the `X-Signature-SHA256` header with Wise's published RSA public keys (`sandbox()` / `production()`), supports multiple keys for rotation, and requires `ext-openssl`.
- `CreateWebhookSubscriptionRequest::for()` builds the documented subscription body: `name`, `trigger_on`, `delivery.version`, `delivery.url`.
- `WebhookEvent` now exposes `schemaVersion`, `subscriptionId`, and the event `data` array.
- `CreateUserTokenRequest::clientCredentials()` for the `client_credentials` grant (application-level tokens).
- `CreateUserTokenRequest::registrationCodeForEmail()`, which sends the `email` field that Wise requires for the `registration_code` grant.
- `CreateUserTokenRequest::basicAuthHeader()`.
- `UserToken` now exposes `expiresAt`, `refreshTokenExpiresIn`, `refreshTokenExpiresAt`, `scope`, and `createdAt`, plus an `expiresAt()` helper that falls back to `expires_in` when `expires_at` is missing.
- `Auth\ClientCredentialsTokenProvider` fetches client credentials tokens and caches them in memory until shortly before they expire.
- Recipient account requirements endpoints (`requirementsForQuote`, `refreshRequirementsForQuote`, `requirementsForRoute`) with dynamic-form models (`AccountRequirement`, `RequirementFieldGroup`, `RequirementField`, `AllowedValue`) and `Accept-Minor-Version: 1` support.
- Optional `ownedByCustomer` parameter on `CreateRecipientAccountRequest`.

### Deprecated
- `WebhookVerifier` (HMAC-SHA256 with a shared secret). Wise signs webhook events with an RSA private key; use `WebhookSignatureVerifier` instead. The class keeps its existing behaviour until the next major release.
- The `CreateWebhookSubscriptionRequest` constructor, in favour of `CreateWebhookSubscriptionRequest::for()`.
- `CreateUserTokenRequest::registrationCode()`. It does not send the user's `email`, which Wise requires for this grant, so the request cannot succeed. Use `registrationCodeForEmail()` instead.

### Fixed
- Webhook documentation and examples no longer describe an unsupported HMAC shared-secret verification flow.
- `userTokens()->create()` now sends client credentials as HTTP Basic auth (`Authorization: Basic base64(client_id:client_secret)`), as Wise documents for `POST /oauth/token`. `client_secret` is no longer sent in the form body for any grant. `client_id` is sent in the body only for the `authorization_code` and `registration_code` grants, where Wise lists it.

## [0.1.0] - 2026-02-23
- Initial production-ready SDK scaffold.
- Transport-agnostic architecture with rich immutable models.
- Quote, Recipient Account, Transfer, Payment, Webhook, Activity, and Profile resources.
- Dual auth support for API token and OAuth2 access-token providers.
- Hardened middleware and exception mapping (sanitized logging, retry-after parsing).
- Added transfer requirements endpoint and stricter transport/error regression tests.
- Added `activity()->iterate()` cursor iterator helper.
- Added webhook replay protection utility (`WebhookReplayProtector`) and in-memory replay store.
- Added `docs/API_REFERENCE.md` endpoint reference.
- Added manual GitHub Actions sandbox check workflow and runbook (`docs/SANDBOX_CHECKS.md`).
