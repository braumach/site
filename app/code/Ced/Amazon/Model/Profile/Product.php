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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\Profile;

/**
 * Class Product
 * @package Ced\Amazon\Model\Profile
 */
class Product extends \Magento\Framework\Model\AbstractModel implements \Ced\Amazon\Api\Data\Profile\ProductInterface
{
    const NAME = 'ced_amazon_profile_product';
    const COLUMN_ID = 'id';
    const COLUMN_PRODUCT_ID = 'product_id';
    const COLUMN_PROFILE_ID = 'profile_id';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Ced\Amazon\Model\ResourceModel\Profile\Product::class);
    }
}
