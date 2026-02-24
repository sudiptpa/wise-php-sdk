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

## Notes

- Use dedicated sandbox credentials with least privilege.
- Do not use production secrets in CI.
