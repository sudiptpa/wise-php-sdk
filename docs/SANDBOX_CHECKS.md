# Sandbox Checks

This repository includes one sandbox workflow:
- `.github/workflows/sandbox-check.yml` (quick check)

## Quick sandbox check

The quick sandbox check runs:
1. `profile.list`
2. `quote.createAuthenticated`
3. `activity.list`

## Trigger

Run from GitHub Actions:
- `sandbox-check`

The workflow runs nightly and supports manual trigger.

## Required secrets

Common:
- `WISE_BASE_URL` (recommended: `https://api.wise-sandbox.com`)
- `WISE_PROFILE_ID`
- `WISE_SOURCE_CURRENCY` (recommended: `USD`)
- `WISE_TARGET_CURRENCY` (recommended: `EUR`)
- `WISE_TARGET_AMOUNT` (recommended: `10`)

For `api_token` mode:
- `WISE_API_TOKEN`

For `oauth2` mode:
- `WISE_ACCESS_TOKEN` (or refresh settings below)
- `WISE_CLIENT_ID`
- `WISE_CLIENT_SECRET`
- `WISE_REFRESH_TOKEN` (optional)
- `WISE_OAUTH_TOKEN_URL` (optional; defaults to Wise OAuth token endpoint)

## Notes

- Use dedicated sandbox credentials with least privilege.
- Do not use production secrets in CI.
- Keep logs masked. Do not print tokens, client secrets, or full webhook payloads in workflow output.
- Keep this workflow lightweight and deterministic: profile list, quote create, activity list.
- Personal token mode is the default path for self-account checks.
- OAuth2 mode should be treated as partner-oriented and may not be available unless Wise has enabled it for your setup.
- Funding flows are intentionally excluded from this quick sandbox check.
