# ETechFlow_AccountLinksManager

Hide unwanted links from the customer **My Account** sidebar in Magento without editing templates or layout XML. Pure admin config, zero frontend assets, Hyvä-safe by design.

Commercial eTechFlow module. Per-domain HMAC license or eTechFlow bundle key activates the module on your production host. Dev / staging / `*.magento.cloud` / `localhost` etc. auto-detect and bypass licensing.

## What it does

Two modes:

| Mode | Behaviour |
|---|---|
| **Hide selected links** | Every link picked is hidden from the customer sidebar; the rest stays visible. |
| **Show only selected links** | Only the picked links remain; everything else is hidden. |

Standard Magento + Adobe Commerce link names are in the multi-select. For third-party extension links, use the **Extra block names** textarea — list any layout block name and the module manages it.

## Features

| | |
|---|---|
| Hide individual customer dashboard links | ✓ |
| Inverse mode (show only the picked ones) | ✓ |
| Configure entirely from admin — zero coding | ✓ |
| Works on Magento Open Source + Adobe Commerce + Hyvä | ✓ |
| Per-store-view configuration | ✓ |
| Custom-extension links via the textarea | ✓ |
| Per-domain HMAC licensing + bundle key support | ✓ |
| Tideways span instrumentation (`ETechFlow_ALM_FilterNav`) | ✓ |
| Verify CLI (`etechflow:alm:verify`) | ✓ |
| No DB tables, no frontend JS, no CSS | ✓ |

## Compatibility

| Platform | Status |
|---|---|
| Magento Open Source 2.4.4 – 2.4.8 | ✓ |
| Adobe Commerce 2.4.4 – 2.4.8 | ✓ (includes Reward Points, Gift Card, RMA, Store Credit, Recurring Payments, Invitations) |
| Hyvä-themed storefronts | ✓ (Hyvä keeps the same `Html\Links` block class) |
| PHP 8.1 / 8.2 / 8.3 / 8.4 | ✓ |

## Installation

```bash
# Option A — Composer
composer require etechflow/module-account-links-manager:^1.0
bin/magento module:enable ETechFlow_AccountLinksManager
bin/magento setup:upgrade
bin/magento setup:di:compile      # production mode only
bin/magento cache:flush

# Option B — Manual drop-in
cp -r ETechFlow/AccountLinksManager app/code/ETechFlow/AccountLinksManager
bin/magento module:enable ETechFlow_AccountLinksManager
bin/magento setup:upgrade
bin/magento setup:di:compile      # production mode only
bin/magento cache:flush
```

No database tables are created — settings live in `core_config_data`.

## Configuration

**Admin → Stores → Configuration → eTechFlow → Customer Dashboard Links Manager**

| Field | Description |
|---|---|
| **License → Production Environment** | Yes for live sites, No for dev/staging on non-standard domains. |
| **License → License Key** | Paste your per-domain key (or bundle key under any eTechFlow module). |
| **General → Enable Module** | Master switch — turns the filtering on/off without uninstalling. |
| **General → Action** | Hide selected / Show only selected. |
| **General → Links** | Multi-select of standard Magento + Adobe Commerce links. |
| **General → Extra block names** | Newline-separated block names for third-party extension links. |

Per-store-view configuration is supported.

## Smoke test

```bash
bin/magento etechflow:alm:verify
```

Should print `✅ ALL CHECKS PASSED. v1.0.0 verified.`

## How it works

The module registers a plugin on `Magento\Framework\View\Element\Html\Links::beforeToHtml()` (frontend-scoped DI). When the customer-account navigation block is about to render:

1. Check the module is enabled + licensed.
2. Read the configured action mode + managed block-name list.
3. For each child link block, decide if it should be removed (based on mode).
4. Remove via `Layout::unsetChild()` — the same mechanism `<referenceBlock remove="true"/>` uses in layout XML.

Result: the link never renders. No HTML rewriting, no CSS hiding.

## Why Hyvä-safe

Hyvä replaces storefront templates and JS but keeps the PHP block classes. The customer-account navigation is still rendered by a class extending `Magento\Framework\View\Element\Html\Links`. Our plugin hooks the parent class and guards by `getNameInLayout() === 'customer_account_navigation'` — works on every theme, doesn't touch footer or other Links blocks.

The module ships zero frontend assets: no JS, no CSS, no `.phtml` overrides. Nothing for Hyvä to clash with.

## Uninstall

```bash
bin/magento module:disable ETechFlow_AccountLinksManager
bin/magento cache:flush

# Composer:
composer remove etechflow/module-account-links-manager
# Manual:
rm -rf app/code/ETechFlow/AccountLinksManager
bin/magento setup:upgrade
bin/magento cache:flush
```

Optional cleanup of leftover config entries:

```sql
DELETE FROM core_config_data WHERE path LIKE 'etechflow_accountlinks/%';
```

## License

Proprietary — see `LICENSE.txt`. Commercial licenses available at <https://etechflow.com>.
