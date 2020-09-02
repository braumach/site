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
 * Interface PriceInterface
 * @package Ced\Amazon\Api\Data
 * @api
 */
interface PriceInterface
{
    const COLUMN_STRATEGY_PRICE_ID = 'id';
    const COLUMN_PRICE_RELATION_ID = 'strategy_id';
}
