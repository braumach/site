<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\Source\Strategy;

use Ced\Amazon\Model\Source\Strategy\Type as StrategyType;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Type
 * @package Ced\Amazon\Model\Source
 */
class Type extends AbstractSource
{
    const STRATEGY_INVENTORY = 'inventory';
    const STRATEGY_PRICE = 'price';
    const STRATEGY_ATTRIBUTE = 'attribute';
    const STRATEGY_SHIPPING = 'shipping';
    const STRATEGY_GLOBAL = 'global_strategy';
    const STRATEGY_ASSIGNMENT = 'assignment';

    const AVAILABLE_STRATEGIES = [
        self::STRATEGY_GLOBAL,
        self::STRATEGY_INVENTORY,
        self::STRATEGY_ATTRIBUTE,
        self::STRATEGY_PRICE,
        self::STRATEGY_SHIPPING,
        self::STRATEGY_ASSIGNMENT,
    ];

    public $config;

    public function __construct(
        \Ced\Amazon\Helper\Config $config
    ) {
        $this->config = $config;
    }

    public static function getTypeList()
    {
        return [
            [
                'value' => StrategyType::STRATEGY_INVENTORY,
                'label' => __('Inventory'),
            ],
            [
                'value' => StrategyType::STRATEGY_ATTRIBUTE,
                'label' => __('Attribute'),
            ],
            [
                'value' => StrategyType::STRATEGY_PRICE,
                'label' => __('Price'),
            ],
            [
                'value' => StrategyType::STRATEGY_SHIPPING,
                'label' => __('Shipping'),
            ],
            [
                'value' => StrategyType::STRATEGY_ASSIGNMENT,
                'label' => __('Assignment'),
            ],
            [
                'value' => StrategyType::STRATEGY_GLOBAL,
                'label' => __('Global Strategy'),
            ]
        ];
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $options = self::getTypeList();
        $allowed = $this->config->getStrategyType();
        foreach ($options as $id => $option) {
            if (!in_array($option['value'], $allowed)) {
                unset($options[$id]);
            }
        }

        return $options;
    }
}
