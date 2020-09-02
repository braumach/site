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
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\ResourceModel;

/**
 * Class Product
 * @package Ced\Amazon\Model\ResourceModel
 * @method save(\Ced\Amazon\Api\Data\ProductInterface $productInterface)
 * @method load(\Ced\Amazon\Api\Data\ProductInterface $productInterface, $field, $column)
 */
class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const PAGE_SIZE = 1;

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Ced\Amazon\Model\Product::NAME,
            \Ced\Amazon\Model\Product::COLUMN_ID
        );
    }
}
