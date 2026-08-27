<?php

declare(strict_types=1);

namespace ETechFlow\AccountLinksManager\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Multiselect source for the admin "Links" field.
 *
 * Each option value is the VISIBLE LINK LABEL exactly as it appears in the
 * storefront "My Account" sidebar. The frontend plugin removes a link when a
 * managed token matches either the link's block name OR its rendered label, so
 * using the label as the value means the selection works across Magento Open
 * Source, Adobe Commerce and third-party links regardless of the internal
 * block-name convention each of them uses. Links not listed here (custom or
 * third-party) can still be hidden via the "Extra links" textarea by entering
 * their visible label or block name.
 */
class AvailableLinks implements OptionSourceInterface
{
    /** @var array<int, array{value: string, label: \Magento\Framework\Phrase|string}> */
    private array $cache = [];

    /**
     * @return array<int, array{value: string, label: \Magento\Framework\Phrase|string}>
     */
    public function toOptionArray(): array
    {
        if ($this->cache) {
            return $this->cache;
        }

        $this->cache = [
            // Magento Open Source (core) sidebar links
            ['value' => 'My Account',                  'label' => __('My Account / Account Dashboard')],
            ['value' => 'Account Information',          'label' => __('Account Information')],
            ['value' => 'Address Book',                'label' => __('Address Book')],
            ['value' => 'My Orders',                   'label' => __('My Orders')],
            ['value' => 'My Downloadable Products',    'label' => __('My Downloadable Products')],
            ['value' => 'My Product Reviews',          'label' => __('My Product Reviews')],
            ['value' => 'My Wish List',                'label' => __('My Wish List')],
            ['value' => 'Newsletter Subscriptions',    'label' => __('Newsletter Subscriptions')],
            ['value' => 'Stored Payment Methods',      'label' => __('Stored Payment Methods (Vault)')],
            ['value' => 'Billing Agreements',          'label' => __('Billing Agreements')],
            ['value' => 'My Returns',                  'label' => __('My Returns')],

            // Adobe Commerce (Enterprise) sidebar links
            ['value' => 'Store Credit',                'label' => __('Store Credit (Adobe Commerce)')],
            ['value' => 'Reward Points',               'label' => __('Reward Points (Adobe Commerce)')],
            ['value' => 'Gift Card',                   'label' => __('Gift Card (Adobe Commerce)')],
            ['value' => 'Gift Registries',             'label' => __('Gift Registries (Adobe Commerce)')],
            ['value' => 'Order by SKU',                'label' => __('Order by SKU (Adobe Commerce)')],
            ['value' => 'My Invitations',              'label' => __('My Invitations (Adobe Commerce)')],
            ['value' => 'Recurring Billing',           'label' => __('Recurring Billing (Adobe Commerce)')],
        ];

        return $this->cache;
    }
}
