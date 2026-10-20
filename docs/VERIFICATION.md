# Verification notes

## Verified execution — 20 October 2026

The first complete source commit `46740dcb1c1e5be00892cfc2e06da8f884ac8a31` passed GitHub Actions run `39410792652`:
- MySQL 8.4 integration tests: **34 tests, 118 assertions, zero errors, failures or skipped tests**.
- PHP syntax, Blade compilation and route-cache generation passed.
- Real Chromium browser login, CSRF-protected facility creation, role-page rendering, mobile viewport overflow and sidebar interaction passed.
- Composer security audit passed for the resolved dependency set at the time of the run.
- Desktop and mobile screenshots and JUnit output were produced by the running application, not a static mockup.

Source files uploaded in that commit were verified byte-for-byte using the matching local Git tree SHA `f23fc2cc82e88c85b37aaf19f6e6faeb3c397fc6`.

Visual review identified cramped table badges on mobile despite no page overflow. A follow-up responsive stylesheet keeps tables readable inside horizontally scrollable, keyboard-focusable containers. Subsequent commits must pass their own Actions run; the initial run above does not automatically certify later changes.

The authoring workspace cannot run Laravel locally because Composer, PDO drivers and external DNS are unavailable. Full runtime tests ran in GitHub Actions instead.

## Limits

These tests do not establish medical validation, production readiness, regulatory approval, live hospital connectivity, penetration-test coverage or a performance SLA. No clinical blood inventory, device integration, transfusion or release logic is present. True multi-process concurrency stress, disaster-recovery drills and an independent security audit remain required before real patient/donor use.
