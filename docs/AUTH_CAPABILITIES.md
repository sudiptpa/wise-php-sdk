# Auth Capability Guide

This SDK supports both Wise auth styles at code level:
- personal API token
- OAuth2 access token

Wise does not treat them as equivalent in practice.

## Personal API Token

Use this when you are automating your own Wise account.

Typical fit:
- small business or individual account automation
- listing your own profiles
- creating quotes
- creating recipient accounts
- creating transfer drafts
- reading activities and other account data

Important limits:
- personal tokens are not the partner path
- they do not let you manage other Wise customer accounts
- API funding should not be assumed to work with personal tokens

For this SDK, treat personal-token funding as unavailable unless Wise has explicitly confirmed your account setup supports it.

## OAuth2

Use this when you are building against Wise Platform / partner flows.

Typical fit:
- connected-account or delegated-account integrations
- OAuth app credentials (`clientId` / `clientSecret`)
- token refresh handling
- API funding flows that require partner access
- application-level webhook subscriptions

Important limits:
- Wise support has confirmed that OAuth app credentials are provided to approved partner flows
- this is a more complex integration path than personal tokens

## Capability Summary

| Capability | Personal API Token | OAuth2 |
|---|---|---|
| Read your own Wise account data | Yes | Yes |
| Create quotes | Yes | Yes |
| Create recipient accounts | Yes | Yes |
| Create transfer drafts | Yes | Yes |
| Fund transfers through API | Do not rely on this | Yes, partner setup required |
| Manage other Wise customer accounts | No | Yes, partner setup required |
| Exchange OAuth tokens with `/oauth/token` | No | Yes, app credentials required |
| Application-level subscriptions | No | Yes, partner setup required |

## How To Read The SDK Docs

- `token/oauth2` means the SDK can attach either auth style, but Wise account permissions still decide whether the call succeeds.
- `oauth2 partner` means you should treat the endpoint as partner-only.
- `none` means the request itself is unauthenticated, such as token exchange or unauthenticated quote creation.

## Practical Recommendation

If you are integrating only your own Wise account, start with personal token support and document the funding limitation clearly in your app.

If you need connected-account access, API funding flows, or app-level integrations, plan for OAuth2 and Wise partner approval.
