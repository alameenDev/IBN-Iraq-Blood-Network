# Security and privacy boundary

**Pre-production pilot. Not a clinical system or a claim of regulatory compliance.**

Implemented: Laravel session authentication and CSRF middleware, five login attempts/minute per normalized account/IP plus IP cap, password hashing, forced initial password change, database-session revocation, active account/facility middleware, explicit server-side capabilities, tenant-scoped lookups (foreign resources return 404), assignment ceilings for local administrators, immutable system roles, protected self/last-admin handling, strict validated inputs, escaped Blade output, local-only assets, CSP and no-store responses.

Donor identifying fields and patient request references use Laravel authenticated encryption. Exact identity lookup uses a separately configured HMAC key; the blind index is unique within a facility, not a national donor eligibility registry. National administrators cannot inspect donor records with their default role; patient references are withheld in the national request view. Consent records refer to a locally approved document; the software does not approve the legal or clinical wording.

Application audit records have no update/delete UI and the Eloquent model rejects mutations. This is **not** cryptographic tamper evidence, WORM storage or an immutable ledger. Infrastructure administrators and direct SQL remain capable of changes; separate DB privileges, off-host audit export and tamper-evident storage are future requirements. Audit metadata excludes passwords, identity numbers and clinical results. Read access to individual donor/request records is logged; comprehensive access logging for exports and every future integration is not implemented.

No real secrets, donor data, backups or production `.env` may be committed. Public test credentials appearing in tests/CI are isolated fixtures, are never installed by the normal seeder and must not be used in any real deployment. No public self-registration or admin-install web endpoint exists.

Missing before real deployment: MFA/SSO, centrally controlled onboarding and account recovery, retention/erasure policies, validated consent text, penetration test, vulnerability management process, backup/restore validation, monitoring, secret rotation tooling, trusted reverse-proxy configuration, incident response, clinical validation, cross-site donor deferral governance and legal approval. The PHP/MySQL server, TLS, disk/database encryption, backups and physical network are outside this code's guarantees.

Review dependencies and lock versions after a successful connected installation. Do not expose an unsupported PHP runtime. Keep private vulnerability reports out of a public issue if they contain sensitive data.
