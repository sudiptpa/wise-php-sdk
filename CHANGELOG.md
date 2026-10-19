# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added
- `WebhookSignatureVerifier` verifies the `X-Signature-SHA256` header with Wise's published RSA public keys (`sandbox()` / `production()`), supports multiple keys for rotation, and requires `ext-openssl`.
- `CreateWebhookSubscriptionRequest::for()` builds the documented subscription body: `name`, `trigger_on`, `delivery.version`, `delivery.url`.
- `WebhookEvent` now exposes `schemaVersion`, `subscriptionId`, and the event `data` array.

### Deprecated
- `WebhookVerifier` (HMAC-SHA256 with a shared secret). Wise signs webhook events with an RSA private key; use `WebhookSignatureVerifier` instead. The class keeps its existing behaviour until the next major release.
- The `CreateWebhookSubscriptionRequest` constructor, in favour of `CreateWebhookSubscriptionRequest::for()`.

### Fixed
- Webhook documentation and examples no longer describe an unsupported HMAC shared-secret verification flow.

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
