# Architecture

## Principles
- Resources perform HTTP.
- Models are immutable and never perform HTTP.
- Request classes are explicit and human-readable.
- Transport is user-supplied via `TransportInterface`.
- Auth supports both Wise user API tokens and OAuth2 bearer tokens through a shared token-provider contract.

## Layers
- `Wise` / `WiseClient`: factory + orchestration.
- `Transport`: adapters and middleware chain.
- `Resources`: endpoint-level operations.
- `Models`: rich domain objects with helper methods.
- `Hydration`: deterministic mapping from arrays to models.
- `docs/API_REFERENCE.md`: implementation map for supported endpoints.
- `docs/SANDBOX_CHECKS.md`: manual real-sandbox check runbook.

## Safety
- Retry middleware is opt-in and respects `Retry-After`.
- Idempotency middleware is opt-in and applies to POST methods.
- Logging middleware sanitizes sensitive headers and query parameters.
- Rate-limit exceptions expose parsed retry delay from numeric or HTTP-date `Retry-After`.
- Webhook security utilities include signature verification and replay/timestamp checks.
