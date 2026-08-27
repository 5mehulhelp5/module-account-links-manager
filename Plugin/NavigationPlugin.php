<?php
declare(strict_types=1);

namespace ETechFlow\AccountLinksManager\Plugin;

use ETechFlow\AccountLinksManager\Model\Config;
use ETechFlow\AccountLinksManager\Model\LicenseValidator;
use ETechFlow\AccountLinksManager\Model\Performance\Profiler;
use ETechFlow\AccountLinksManager\Model\Source\Mode;
use Magento\Framework\View\Element\Html\Links;

class NavigationPlugin
{
    public function __construct(
        private readonly Config $config,
        private readonly LicenseValidator $licenseValidator
    ) {
    }

    public function beforeToHtml(Links $subject): void
    {
        if ($subject->getNameInLayout() !== 'customer_account_navigation') {
            return;
        }

        // License must be valid (checks IP, domain, revoked flag, and expiry).
        // If invalid for any reason — wrong IP, revoked, expired — module is inactive
        // and default Magento navigation shows unchanged.
        if (!$this->licenseValidator->isValid()) {
            return;
        }

        if (!$this->config->isEnabled()) {
            return;
        }

        $managed = $this->config->getManagedBlockNames();
        if (!$managed) {
            return;
        }

        $layout = $subject->getLayout();
        if (!$layout) {
            return;
        }

        $parent = $subject->getNameInLayout();

        // Normalise every admin-configured token once. A token may be either a
        // layout block name (e.g. "customer-account-navigation-orders-link") OR
        // the visible link label (e.g. "Stored Payment Methods"). We match on
        // BOTH, so a merchant can hide a link by the text they actually see in
        // the sidebar without knowing its internal block name — and it no longer
        // matters that different Magento editions / third-party modules use
        // different block-name conventions (that mismatch was why Adobe Commerce
        // links such as Stored Payment Methods, Store Credit, Reward Points, Gift
        // Card, Gift Registry, Order by SKU and My Invitations could not be hidden).
        $managedKeys = [];
        foreach ($managed as $token) {
            $key = $this->normalize($token);
            if ($key !== '') {
                $managedKeys[$key] = true;
            }
        }
        if (!$managedKeys) {
            return;
        }

        $span = Profiler::start('ETechFlow_ALM_FilterNav');
        try {
            $mode = $this->config->getMode();

            foreach ($layout->getChildNames($parent) as $childName) {
                // A child link is identified by its block name AND its rendered
                // label; either one matching a managed token counts as a match.
                $keys = [$this->normalize((string) $childName)];

                $child = $layout->getBlock($childName);
                if ($child) {
                    $label = $child->getLabel();
                    if ($label !== null && $label !== false && (string) $label !== '') {
                        $keys[] = $this->normalize((string) $label);
                    }
                }

                $isManaged = false;
                foreach ($keys as $key) {
                    if ($key !== '' && isset($managedKeys[$key])) {
                        $isManaged = true;
                        break;
                    }
                }

                $shouldRemove = ($mode === Mode::HIDE_SELECTED && $isManaged)
                    || ($mode === Mode::SHOW_ONLY && !$isManaged);

                if ($shouldRemove) {
                    $layout->unsetChild($parent, $childName);
                }
            }
        } finally {
            Profiler::stop($span);
        }
    }

    /**
     * Reduce a block name or a link label to a comparison key: lower-cased, with
     * hyphens, underscores and runs of whitespace collapsed to a single space.
     * This lets "Stored Payment Methods", "stored payment methods" and the block
     * name "customer-account-navigation-my-credit-cards-link" all be compared on
     * an equal footing against both a link's block name and its visible label.
     */
    private function normalize(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[\s\-_]+/', ' ', $value) ?? $value;

        return trim($value);
    }
}
