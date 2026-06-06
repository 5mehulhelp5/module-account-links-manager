<?php

declare(strict_types=1);

namespace ETechFlow\AccountLinksManager\Block\Adminhtml\License;

use ETechFlow\AccountLinksManager\Model\LicenseValidator;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class Gate extends Template
{
    private const PORTAL_URL_PATH     = 'etechflow_accountlinks/license/portal_url';
    private const PORTAL_API_URL_PATH = 'etechflow_accountlinks/license/portal_api_url';

    public function __construct(
        Context $context,
        private readonly LicenseValidator $licenseValidator,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getFormKey(): string
    {
        // $this->formKey is the protected property from Magento\Framework\View\Element\AbstractBlock
        if ($this->formKey !== null) {
            return $this->formKey->getFormKey();
        }
        return \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Data\Form\FormKey::class)
            ->getFormKey();
    }

    public function getConfigUrl(): string
    {
        return (string) $this->getUrl(
            'adminhtml/system_config/edit',
            ['section' => 'etechflow_accountlinks', '_fragment' => 'etechflow_accountlinks_license-head']
        );
    }

    public function getPortalBrowserUrl(): string
    {
        $v = (string) $this->_scopeConfig->getValue(self::PORTAL_URL_PATH);
        return rtrim($v, '/');
    }

    /**
     * Portal /license/plans endpoint for this module + domain. The portal admin
     * decides recurring vs one-time, so the gate renders the matching cards.
     */
    public function getPlansUrl(): string
    {
        $api = trim((string) $this->_scopeConfig->getValue(self::PORTAL_API_URL_PATH));
        if ($api === '') {
            $api = trim((string) $this->_scopeConfig->getValue(self::PORTAL_URL_PATH));
        }
        $api = rtrim($api, '/');
        if ($api === '') {
            return '';
        }
        return $api . '/license/plans?module=account-links-manager&domain='
            . urlencode($this->licenseValidator->getCurrentHost());
    }

    public function getSelectPlanUrl(string $plan = ''): string
    {
        $base = $this->getPortalBrowserUrl();
        if ($base === '') {
            return '#portal-not-configured';
        }
        $domain      = $this->licenseValidator->getCurrentHost();
        $returnUrl   = (string) $this->getUrl('etechflow_accountlinks/license/index');
        $settingsUrl = (string) $this->getUrl(
            'adminhtml/system_config/edit',
            ['section' => 'etechflow_accountlinks', '_fragment' => 'etechflow_accountlinks_license-head']
        );
        $params = http_build_query([
            'domain'       => $domain,
            'module'       => 'account-links-manager',
            'platform'     => 'magento',
            'return_url'   => $returnUrl,
            'settings_url' => $settingsUrl,
        ]);
        if ($plan !== '') {
            $params .= '&plan=' . urlencode($plan);
        }
        return $base . '/select-plan?' . $params;
    }

    public function getCurrentDomain(): string
    {
        return $this->licenseValidator->getCurrentHost();
    }

    public function getConfiguredKey(): string
    {
        return $this->licenseValidator->getConfiguredKey();
    }

    public function isPortalConfigured(): bool
    {
        return $this->getPortalBrowserUrl() !== '';
    }
}