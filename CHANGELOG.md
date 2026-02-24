# Changelog

All notable changes to this project will be documented in this file.

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
