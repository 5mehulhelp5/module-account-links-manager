# Changelog — ETechFlow Account Links Manager

All notable changes to this module. Adheres to [Semantic Versioning](https://semver.org/).

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
