# Sandbox Checks

This repository includes two sandbox workflows:
- `.github/workflows/sandbox-check.yml` (quick check)
- `.github/workflows/sandbox-full-check.yml` (full check)

## Quick sandbox check

The quick sandbox check runs:
1. `profile.list`
2. `quote.createAuthenticated`
3. `activity.list`

## Full sandbox check

The full sandbox check runs every implemented endpoint in `docs/API_REFERENCE.md`.
It makes real sandbox calls for:
- Quote: create unauthenticated/authenticated, get, update
- Recipient Account: create, list, get
- Transfer: create, get, requirements
- Payment: fund transfer (can return 4xx based on Wise business rules)
- Webhook: application + profile create/list/get/delete and app test notification
- Activity: list, iterate
- Profile: list, get

## Trigger

Run from GitHub Actions:
- `sandbox-check`
- `sandbox-full-check`

Both workflows accept `auth_mode`:
- `api_token`
- `oauth2`

## Required secrets

Common:
- `WISE_BASE_URL` (recommended: `https://api.wise-sandbox.com`)
- `WISE_PROFILE_ID`
- `WISE_SOURCE_CURRENCY` (recommended: `USD`)
- `WISE_TARGET_CURRENCY` (recommended: `EUR`)
- `WISE_SOURCE_AMOUNT` (recommended: `10`)
- `WISE_TARGET_AMOUNT` (recommended: `10`)

For `api_token` mode:
- `WISE_API_TOKEN`

For `oauth2` mode:
- Option A: `WISE_ACCESS_TOKEN`
- Option B (refresh flow):
  - `WISE_REFRESH_TOKEN`
  - `WISE_CLIENT_ID`
  - `WISE_CLIENT_SECRET`
  - `WISE_OAUTH_TOKEN_URL`

Extra required for the full sandbox check:
- `WISE_RECIPIENT_CURRENCY`
- `WISE_RECIPIENT_TYPE` (for example `iban`)
- `WISE_RECIPIENT_HOLDER_NAME`
- `WISE_RECIPIENT_DETAILS_JSON` (JSON object for recipient details expected by Wise for your corridor)
- `WISE_CLIENT_KEY` (for application-level webhook endpoints)
- `WISE_WEBHOOK_URL` (public https webhook target)

## Notes

- Use dedicated sandbox credentials with least privilege.
- Do not use production secrets in CI.
- The full sandbox check performs write operations in sandbox.
