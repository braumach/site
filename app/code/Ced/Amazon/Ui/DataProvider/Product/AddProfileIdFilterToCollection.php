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

namespace Ced\Amazon\Ui\DataProvider\Product;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

class AddProfileIdFilterToCollection implements AddFilterToCollectionInterface
{
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        if (isset($condition['eq'])) {
            $collection->getSelect()->where(
                \Ced\Amazon\Api\Data\Profile\ProductInterface::PROFILE_PRODUCT_TABLE_ALIAS . '.profile_id = ?',
                (float)$condition['eq']
            );
        }
        if (isset($condition['in'])) {
            $collection->getSelect()->where(
                \Ced\Amazon\Api\Data\Profile\ProductInterface::PROFILE_PRODUCT_TABLE_ALIAS . '.profile_id = ?',
                (float)$condition['in']
            );
        }

    }
}
