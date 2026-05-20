<?php

declare(strict_types=1);

namespace ETechFlow\AccountLinksManager\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Multiselect source for the admin "Links" field.
 *
 * Lists the layout block names that standard Magento Open Source +
 * Adobe Commerce register for the customer-account sidebar navigation.
 * Stores with custom-extension links can add extra block names via the
 * "Extra block names" textarea on the config page — no code change.
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
            // Core Magento Open Source
            ['value' => 'customer-account-navigation-account-link',                   'label' => __('Account Dashboard')],
            ['value' => 'customer-account-navigation-account-edit-link',              'label' => __('Account Information')],
            ['value' => 'customer-account-navigation-address-link',                   'label' => __('Address Book')],
            ['value' => 'customer-account-navigation-orders-link',                    'label' => __('My Orders')],
            ['value' => 'customer-account-navigation-downloadable-products-link',     'label' => __('My Downloadable Products')],
            ['value' => 'customer-account-navigation-product-reviews-link',           'label' => __('My Product Reviews')],
            ['value' => 'customer-account-navigation-wish-list-link',                 'label' => __('My Wish List')],
            ['value' => 'customer-account-navigation-share-wishlist-link',            'label' => __('Share My Wish List')],
            ['value' => 'customer-account-navigation-newsletter-subscriptions-link',  'label' => __('Newsletter Subscriptions')],
            ['value' => 'customer-account-navigation-stored-payment-methods-link',    'label' => __('Stored Payment Methods')],
            ['value' => 'customer-account-navigation-billing-agreements-link',        'label' => __('Billing Agreements')],
            ['value' => 'customer-account-navigation-compare-link',                   'label' => __('Compare Products')],

            // Adobe Commerce additional links
            ['value' => 'customer-account-navigation-rewards-link',                   'label' => __('Reward Points (Adobe Commerce)')],
            ['value' => 'customer-account-navigation-gift-card-info-link',            'label' => __('Gift Card (Adobe Commerce)')],
            ['value' => 'customer-account-navigation-gift-registry-link',             'label' => __('Gift Registries (Adobe Commerce)')],
            ['value' => 'customer-account-navigation-rma-link',                       'label' => __('My Returns / RMA (Adobe Commerce)')],
            ['value' => 'customer-account-navigation-store-credit-link',              'label' => __('Store Credit (Adobe Commerce)')],
            ['value' => 'customer-account-navigation-recurring-payments-link',        'label' => __('Recurring Payments (Adobe Commerce)')],
            ['value' => 'customer-account-navigation-invitation-link',                'label' => __('Invitations (Adobe Commerce)')],

            // Common third-party integrations
            ['value' => 'customer-account-navigation-magefan-blog-comments-link',     'label' => __('Magefan Blog Comments')],
            ['value' => 'customer-account-navigation-amasty-mostviewed-link',         'label' => __('Amasty Most Viewed')],
        ];

        return $this->cache;
    }
}
