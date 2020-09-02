<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Ui\DataProvider\Profile\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class StrategyModifier implements ModifierInterface
{
    const FIELDSET_STRATEGIES = "strategies";
    const FIELDSET_PRODUCTS = "profile_products";

    public $config;

    public function __construct(
        \Ced\Amazon\Helper\Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param array $data
     * @return array
     * @since 100.1.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     * @since 100.1.0
     */
    public function modifyMeta(array $meta)
    {
        if ($this->config->getStrategyAssignByRule()) {
            $meta[self::FIELDSET_STRATEGIES]['children']['strategy']['arguments']['data']['config']['default'] = "1";
            $meta[self::FIELDSET_STRATEGIES]['children']['profile_strategy_attribute']['arguments']['data']['config']['visible'] = 0;

            $meta[self::FIELDSET_STRATEGIES]['children']['profile_strategy_price']['arguments']['data']['config']['visible'] = 0;
            $meta[self::FIELDSET_STRATEGIES]['children']['profile_strategy_price']['children']['strategy_price']['arguments']['data']['config']['visible'] = 0;
            $meta[self::FIELDSET_STRATEGIES]['children']['profile_strategy_price']['children']['create_price_strategy_button']['arguments']['data']['config']['visible'] = 0;

            $meta[self::FIELDSET_STRATEGIES]['children']['profile_strategy_shipping']['arguments']['data']['config']['visible'] = 0;
            $meta[self::FIELDSET_STRATEGIES]['children']['profile_strategy_shipping']['children']['strategy_shipping']['arguments']['data']['config']['visible'] = 0;
            $meta[self::FIELDSET_STRATEGIES]['children']['profile_strategy_shipping']['children']['create_shipping_strategy_button']['arguments']['data']['config']['visible'] = 0;

            $meta[self::FIELDSET_STRATEGIES]['children']['profile_strategy_inventory']['arguments']['data']['config']['visible'] = 0;
            $meta[self::FIELDSET_STRATEGIES]['children']['profile_strategy_inventory']['children']['strategy_inventory']['arguments']['data']['config']['visible'] = 0;
            $meta[self::FIELDSET_STRATEGIES]['children']['profile_strategy_inventory']['children']['create_strategy_button']['arguments']['data']['config']['visible'] = 0;

            $meta[self::FIELDSET_STRATEGIES]['children']['profile_strategy_attribute']['arguments']['data']['config']['visible'] = 0;
            $meta[self::FIELDSET_STRATEGIES]['children']['profile_strategy_attribute']['children']['strategy_attribute']['arguments']['data']['config']['visible'] = 0;
            $meta[self::FIELDSET_STRATEGIES]['children']['profile_strategy_attribute']['children']['create_attribute_strategy_button']['arguments']['data']['config']['visible'] = 0;
        }

        return $meta;
    }
}
