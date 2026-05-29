<?php

declare(strict_types=1);

namespace ETechFlow\AccountLinksManager\Model;

use ETechFlow\AccountLinksManager\Model\Source\Mode;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Admin-config wrapper for ETechFlow_AccountLinksManager.
 *
 * `isEnabled()` consults the license validator first — an unlicensed
 * install silently disables the sidebar filter regardless of the
 * admin Enable toggle.
 */
class Config
{
    public const XML_ENABLED      = 'etechflow_accountlinks/general/enabled';
    public const XML_MODE         = 'etechflow_accountlinks/general/mode';
    public const XML_HIDDEN_LINKS = 'etechflow_accountlinks/general/hidden_links';
    public const XML_EXTRA_BLOCKS = 'etechflow_accountlinks/general/extra_block_names';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param LicenseValidator     $licenseValidator
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly LicenseValidator $licenseValidator
    ) {
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled(?int $storeId = null): bool
    {
        if (!$this->licenseValidator->isValid()) {
            return false;
        }
        return $this->scopeConfig->isSetFlag(self::XML_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Raw feature toggle — no license check.
     * Use in frontend plugins where the admin has already configured the feature;
     * license enforcement is handled by the admin gate page.
     */
    public function isFeatureEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string Either Mode::HIDE_SELECTED or Mode::SHOW_ONLY.
     */
    public function getMode(?int $storeId = null): string
    {
        $val = (string) $this->scopeConfig->getValue(self::XML_MODE, ScopeInterface::SCOPE_STORE, $storeId);
        return $val !== '' ? $val : Mode::HIDE_SELECTED;
    }

    /**
     * Return all block names the admin configured: multi-select choices
     * + extra-blocks textarea entries, de-duplicated.
     *
     * @param int|null $storeId
     * @return string[]
     */
    public function getManagedBlockNames(?int $storeId = null): array
    {
        $raw   = (string) $this->scopeConfig->getValue(self::XML_HIDDEN_LINKS, ScopeInterface::SCOPE_STORE, $storeId);
        $extra = (string) $this->scopeConfig->getValue(self::XML_EXTRA_BLOCKS, ScopeInterface::SCOPE_STORE, $storeId);

        $selected = array_filter(array_map('trim', explode(',', $raw)));
        $custom   = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $extra)));

        return array_values(array_unique(array_merge($selected, $custom)));
    }
}