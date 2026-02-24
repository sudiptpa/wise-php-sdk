# Release Checklist

Use this checklist for every tagged release.

## 1) Prepare
- [ ] Work on `main` is clean and CI is green
- [ ] `composer qa` passes locally
- [ ] No pending high-severity issues

## 2) Verify package quality
- [ ] Run unit test suite
- [ ] Run static analysis
- [ ] Run coding standard check
- [ ] Review new/changed public APIs for BC impact

## 3) Update docs
- [ ] Update `CHANGELOG.md` with release notes
- [ ] Update README/examples if behavior changed
- [ ] Confirm endpoint docs in `docs/API_REFERENCE.md` are accurate

## 4) Version decision
- [ ] Choose version bump type:
  - [ ] PATCH
  - [ ] MINOR
  - [ ] MAJOR
- [ ] Confirm SemVer rationale

## 5) Tag and publish
- [ ] Create git tag (e.g. `v0.2.0`)
- [ ] Push tag to GitHub
- [ ] Create GitHub Release with changelog summary
- [ ] Verify Packagist sync (if enabled)

## 6) Post-release checks
- [ ] Install package in a clean sample project
- [ ] Confirm latest tag appears on Packagist
- [ ] Announce release (if applicable)
