# Release Checklist

Use this checklist before creating any tag.

## 1) Starting checks
- [ ] Worktree is clean on `main`
- [ ] CI is green for required jobs
- [ ] No open high-severity bugs/security issues

## 2) Quality checks
- [ ] `composer validate --strict`
- [ ] `composer qa`
- [ ] `composer audit --no-dev`

## 3) Endpoint verification checks
- [ ] `tests/Unit/Resources/EndpointPathAllowlistTest.php` passes
- [ ] `docs/API_REFERENCE.md` matches implemented endpoints
- [ ] `sandbox-check` workflow passes against sandbox credentials

## 4) Docs and changelog
- [ ] `CHANGELOG.md` updated
- [ ] README/examples updated for any behavior changes
- [ ] Security/upgrade notes updated when required

## 5) Versioning
- [ ] Decide SemVer bump (PATCH/MINOR/MAJOR)
- [ ] Confirm BC impact is documented

## 6) Tag and publish
- [ ] Create tag (example: `v0.2.0`)
- [ ] Push tag to GitHub
- [ ] Create GitHub release notes
- [ ] Confirm Packagist sync

## 7) Post-release checks
- [ ] Install in a clean project and run a basic flow
- [ ] Confirm docs links and examples still execute
