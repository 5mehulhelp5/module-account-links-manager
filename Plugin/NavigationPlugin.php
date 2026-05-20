<?php

declare(strict_types=1);

namespace ETechFlow\AccountLinksManager\Plugin;

use ETechFlow\AccountLinksManager\Model\Config;
use ETechFlow\AccountLinksManager\Model\Performance\Profiler;
use ETechFlow\AccountLinksManager\Model\Source\Mode;
use Magento\Framework\View\Element\Html\Links;

/**
 * Removes selected children from the customer "My Account" sidebar before
 * the navigation block renders. Works identically on:
 *   - Magento Open Source 2.4+ (default Luma/Blank themes)
 *   - Adobe Commerce 2.4+
 *   - Hyvä-themed storefronts (Hyvä re-skins the template but keeps the
 *     same Magento\Framework\View\Element\Html\Links block class and
 *     child block names).
 *
 * Hooks the parent class Links::beforeToHtml() so a theme that swaps the
 * specific navigation subclass still gets the filter, as long as it
 * extends Html\Links. The plugin guards by checking the block's
 * name-in-layout so it doesn't touch unrelated lists (footer links, etc.).
 *
 * Removal uses Layout::unsetChild() — the same mechanism layout XML's
 * `<referenceBlock remove="true"/>` uses. Clean and safe.
 */
class NavigationPlugin
{
    /**
     * @param Config $config
     */
    public function __construct(
        private readonly Config $config
    ) {
    }

    /**
     * Filter the navigation block's children based on admin config.
     *
     * @param Links $subject
     * @return void
     */
    public function beforeToHtml(Links $subject): void
    {
        // Only the customer-account navigation list is relevant. Other Links
        // blocks (footer link lists, etc.) keep all their children intact.
        if ($subject->getNameInLayout() !== 'customer_account_navigation') {
            return;
        }
        if (!$this->config->isEnabled()) {
            return;
        }

        $managed = $this->config->getManagedBlockNames();
        if (!$managed) {
            return;
        }

        $span = Profiler::start('ETechFlow_ALM_FilterNav');
        try {
            $mode   = $this->config->getMode();
            $layout = $subject->getLayout();
            if (!$layout) {
                return;
            }

            $parent   = $subject->getNameInLayout();
            $children = $layout->getChildNames($parent);

            foreach ($children as $childName) {
                $isManaged = in_array($childName, $managed, true);

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
}
