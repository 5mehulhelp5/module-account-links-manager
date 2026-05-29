<?php

declare(strict_types=1);

namespace ETechFlow\AccountLinksManager\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model for the admin "Action" dropdown.
 */
class Mode implements OptionSourceInterface
{
    public const HIDE_SELECTED = 'hide_selected';
    public const SHOW_ONLY     = 'show_only';

    /**
     * @return array<int, array{value: string, label: \Magento\Framework\Phrase|string}>
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::HIDE_SELECTED, 'label' => __('Hide selected links')],
            ['value' => self::SHOW_ONLY,     'label' => __('Show only selected links')],
        ];
    }
}