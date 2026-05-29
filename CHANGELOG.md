# Changelog — ETechFlow Account Links Manager

All notable changes to this module. Adheres to [Semantic Versioning](https://semver.org/).

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