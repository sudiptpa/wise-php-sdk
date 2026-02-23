# Sandbox Smoke Tests

This repository includes a manual GitHub Actions smoke test workflow:
- `.github/workflows/sandbox-smoke.yml`

It validates a minimal real-sandbox flow:
1. List profiles
2. Create authenticated quote
3. List activities

## Trigger

Run from GitHub Actions:
- Workflow: `sandbox-smoke`
- Input: `auth_mode`
  - `api_token`
  - `oauth2`

## Required Secrets

Always required:
- `WISE_PROFILE_ID`

Recommended:
- `WISE_BASE_URL` = `https://api.wise-sandbox.com`
- `WISE_SOURCE_CURRENCY` (default `USD`)
- `WISE_TARGET_CURRENCY` (default `EUR`)
- `WISE_TARGET_AMOUNT` (default `10`)

For `api_token` mode:
- `WISE_API_TOKEN`

For `oauth2` mode:
- Option A (direct access token):
  - `WISE_ACCESS_TOKEN`
- Option B (refresh-token flow in workflow):
  - `WISE_REFRESH_TOKEN`
  - `WISE_CLIENT_ID`
  - `WISE_CLIENT_SECRET`
  - `WISE_OAUTH_TOKEN_URL`

## Notes

- The workflow is `workflow_dispatch` only to avoid running on every pull request.
- Use dedicated sandbox credentials with limited scope.
- Do not reuse production credentials in CI.
