# Versioning Policy

This package follows Semantic Versioning (SemVer).

## Version format
`MAJOR.MINOR.PATCH`

## What changes each level

### PATCH (`x.y.Z`)
Backward-compatible bug fixes and internal improvements.
Examples:
- Fix request path/body bug
- Improve error mapping without API changes
- Docs/test updates only

### MINOR (`x.Y.z`)
Backward-compatible feature additions.
Examples:
- New non-deprecated Wise endpoint support
- New helper methods
- New optional middleware/config options

### MAJOR (`X.y.z`)
Breaking changes.
Examples:
- Removed/renamed public methods
- Constructor signature changes
- Model property or type changes that break consumer code
- Removal of previously supported behavior

## Public API surface
Public API includes:
- `Sujip\Wise\*` public classes and methods
- Request/Model constructors and public properties
- Exception types and key public fields
- Documented config options and scripts

## Deprecation policy
- Deprecations are announced in `CHANGELOG.md`.
- Deprecated APIs remain available until the next major release unless there is a security reason.

## Runtime support
- Supported PHP versions are defined in `composer.json` and enforced in CI.
