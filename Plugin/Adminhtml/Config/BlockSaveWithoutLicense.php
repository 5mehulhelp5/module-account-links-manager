<?php
declare(strict_types=1);

namespace ETechFlow\AccountLinksManager\Plugin\Adminhtml\Config;

use ETechFlow\AccountLinksManager\Model\LicenseValidator;
use Magento\Config\Model\Config;
use Magento\Framework\Exception\LocalizedException;

class BlockSaveWithoutLicense
{
    public function __construct(
        private readonly LicenseValidator $licenseValidator
    ) {
    }

    public function beforeSave(Config $subject): void
    {
        if ($subject->getSection() !== 'etechflow_accountlinks') {
            return;
        }
        if (!$this->licenseValidator->isValid()) {
            throw new LocalizedException(
                __('A valid Account Links Manager license is required to save these settings. Please activate your license first.')
            );
        }
    }
}