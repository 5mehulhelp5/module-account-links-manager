# Changelog — ETechFlow Account Links Manager

All notable changes to this module. Adheres to [Semantic Versioning](https://semver.org/).

---

## [Unreleased] — Fixed (Adobe QA follow-up)

### Fixed
- **Links now hide reliably across all Magento editions.** The navigation filter
  matched customer-sidebar links by their internal layout **block name** only,
  using guessed names that were wrong for Adobe Commerce and Vault links — so
  **Stored Payment Methods, Store Credit, Reward Points, Gift Card, Gift
  Registries, Order by SKU and My Invitations** could not be hidden (e.g. Vault's
  real block is `customer-account-navigation-my-credit-cards-link`, not the
  guessed `...stored-payment-methods-link`). The plugin now matches each link by
  its **visible label OR its block name** (case-insensitive, hyphen/space
  tolerant), so a merchant hides a link by the text they actually see.
- **"Links" multiselect** now lists links by their visible label (and works for
  Vault + Adobe Commerce links); the **"Extra links"** textarea now accepts either
  the visible link text or the block name, with in-page help explaining both.

---

## [1.2.0] — 2026-07-03 — Security: portal-only licensing (removes forgeable key path)

Closes a licensing bypass. Previous versions shipped the HMAC signing secret
inside `LicenseValidator` (`SECRET_FRAGMENTS`/`BUNDLE_SECRET_FRAGMENTS`) and
validated a locally-computed key against it, so anyone with the module source
could compute a valid key for their own domain and run the module unlicensed.
A second bypass let the client-settable "locally issued" 48-hour grace
(`issued_key`/`issued_domain`/`stripe_session`/`issued_at`) activate the module
whenever the portal was unreachable — all fields the customer controls.

### Changed (security)

- **Validation is now portal-only.** `isValid()` honours a key only when the
  ETechFlow portal confirms it. The module ships no signing secret.
- Removed `computeKey()`, `computeBundleKey()`, `SECRET_FRAGMENTS`,
  `BUNDLE_SECRET_FRAGMENTS`, and the `isLocallyIssuedKey()` client grace.
- Offline grace now derives solely from a cached genuine portal success and is
  keyed to host+key, so it cannot be fabricated or reused across domains.
- `isProductionEnvironment()` is hardcoded on; the `production_environment=No`
  toggle can no longer bypass licensing.
- Rewrote the unit suite as a portal-only suite, including a hard test that a
  forged `SP-` key with attacker-controlled config and no portal is rejected.

---

## [1.1.0] — 2026-05-29

### Security & Licensing

- **Fixed critical bypass bug**: `isLocallyIssuedKey()` previously recreated the 48-hour grace cache on every `cache:flush`, allowing the local fast-path to bypass IP validation indefinitely. The grace period is now tracked via an `issued_at` config timestamp written once by `Callback.php` at purchase time — no longer resettable by a cache flush.
- **Added `isExplicitlyRevoked()`**: A `revoked = 1` config flag (set by `Revoke.php`) now short-circuits all other checks including dev-mode bypass, ensuring suspended licenses are immediately deactivated.
- **Added `BlockSaveWithoutLicense` plugin**: Hooks `Magento\\Config\\Model\\Config::save()` to block saving `etechflow_accountlinks` config without a valid license, preventing settings from being applied without activation.

### Admin UX

- **License Gate page**: Full dark-navy admin UI at `Stores → Settings → Account Links Manager` showing plan cards, subscription status, and in-Magento Stripe Checkout flow (no external portal redirect required).
- **In-Magento Stripe payment**: `CreateSession` controller builds a Stripe Checkout session server-side; `Callback` (frontend) receives the session and activates the SP-XXXX key.
- **Success page**: Post-purchase success screen shows the activated key with copy button.
- **Fixed form_key injection**: Admin AJAX for Stripe session now uses server-side rendered form key instead of `window.FORM_KEY` fallback.

### Navigation Enforcement

- **NavigationPlugin license check**: `beforeToHtml` now returns early if `LicenseValidator::isValid()` is false — the module is fully dormant when unlicensed.

---

## [1.0.0] — 2026-05-19

### Initial commercial release

Hide unwanted links from the customer **My Account** sidebar without editing templates or layout XML. Two modes:

- **Hide selected links** — every link picked is hidden.
- **Show only selected links** — only the picked links remain.

#### Added

- **Admin config**: `Stores → Configuration → eTechFlow → Customer Dashboard Links Manager`. Enable toggle, mode dropdown, multi-select of standard + Adobe Commerce link names, plus an "Extra block names" textarea for third-party extension links.
- **Per-installation HMAC license** with bundle-key support. Same pattern as every other eTechFlow module.
- **Profiler instrumentation** — wraps the navigation filter in an `ETechFlow_ALM_FilterNav` Tideways span.
- **Verify CLI** — `bin/magento etechflow:alm:verify`.
- **Hyvä-safe** — hooks the parent `Magento\Framework\View\Element\Html\Links` class so Hyvä's re-skinned navigation works without changes.
- **Frontend-only DI registration** — `etc/frontend/di.xml` so the plugin only runs where it matters.

#### Compatibility

- Magento Open Source 2.4.4 – 2.4.8
- Adobe Commerce 2.4.4 – 2.4.8 (includes link names for Reward Points, Gift Card, Gift Registries, RMA, Store Credit, Recurring Payments, Invitations)
- PHP 8.1 / 8.2 / 8.3 / 8.4
- All Hyvä child themes