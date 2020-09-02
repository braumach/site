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
 * @category Ced
 * @package Ced_Amazon
 * @author CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright © 2019 CedCommerce. All rights reserved.
 * @license EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\ResourceModel\Search;

/**
 * Class Product
 * @package Ced\Amazon\Model\ResourceModel\Search
 * @method save(\Ced\Amazon\Api\Data\Search\ProductInterface $object)
 * @method load(\Ced\Amazon\Api\Data\Search\ProductInterface $object, $value, $field = null)
 */
class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Ced\Amazon\Model\Search\Product::NAME,
            \Ced\Amazon\Model\Search\Product::COLUMN_ID
        );
    }
}
