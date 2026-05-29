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

        $span = Profiler::start('ETechFlow_ALM_FilterNav');
        try {
            $mode     = $this->config->getMode();
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