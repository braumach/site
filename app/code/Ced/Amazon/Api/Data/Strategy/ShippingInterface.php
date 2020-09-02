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
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Api\Data\Strategy;

/**
 * Interface ShippingInterface
 * @package Ced\Amazon\Api\Data
 * @api
 */
interface ShippingInterface
{
    const COLUMN_MERCHANT_SHIPPING_GROUP_NAME = 'shipping_group_name';
    const COLUMN_STRATEGY_SHIPPING_ID = 'id';
    const COLUMN_SHIPPING_RELATION_ID = 'strategy_id';
    /**
     * Get Shipping Name
     * @return int
     */
    public function getShippingGroupName();
}
