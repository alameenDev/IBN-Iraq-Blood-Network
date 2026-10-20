# Verification notes

At source-generation time in the assistant workspace:
- PHP CLI syntax checks run over authored PHP files.
- JavaScript syntax checked with Node.
- Dependency installation and MySQL execution are not available locally: the workspace has no working external DNS, Composer or PDO database drivers.
- Therefore no local Laravel runtime, MySQL test pass, medical validation, production readiness, hosted deployment or browser verification is claimed by these static checks.

The included GitHub Actions workflow is intended to run the full PHPUnit integration suite against MySQL 8.4, compile views/routes, execute real browser login and CSRF-protected institution creation, test the mobile menu/overflow, and publish screenshots plus JUnit and composer.lock artifacts. Consult the actual run result. A workflow definition alone is not a successful test run.

Not covered: clinical validation, real blood inventory, device integration, actual ministry/hospital connectivity, load/performance SLA, multi-process contention/failure injection, disaster-recovery drills or independent security audit. These remain required before real patient/donor use.
