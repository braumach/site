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

namespace Ced\Amazon\Ui\DataProvider\Product;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;

/**
 * Class AddProfileIdToCollection
 * @package Ced\Amazon\Ui\DataProvider\Product
 */
class AddProfileIdToCollection implements AddFieldToCollectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $name = $collection->getTable(\Ced\Amazon\Model\Profile\Product::NAME);
        $product = $collection->getTable(\Ced\Amazon\Model\Product::NAME);
        $alias = \Ced\Amazon\Api\Data\Profile\ProductInterface::PROFILE_PRODUCT_TABLE_ALIAS;
        $collection->getSelect()
            ->where($alias.".".\Ced\Amazon\Model\Profile\Product::COLUMN_PROFILE_ID." > 0")
            ->join(
                [$alias => $name],
                $alias.".".\Ced\Amazon\Model\Profile\Product::COLUMN_PRODUCT_ID.'=e.entity_id',
                [
                    \Ced\Amazon\Api\Data\Profile\ProductInterface::ATTRIBUTE_CODE_PROFILE_ID =>
                        new \Zend_Db_Expr(
                            "GROUP_CONCAT(`{$alias}`.profile_id ORDER BY `{$alias}`.profile_id ASC SEPARATOR ',')"
                        ),
                    \Ced\Amazon\Api\Data\Profile\ProductInterface::ATTRIBUTE_CODE_RELATION_ID =>
                        new \Zend_Db_Expr(
                            "GROUP_CONCAT(`{$alias}`.id ORDER BY `{$alias}`.id ASC SEPARATOR ',')"
                        ),
                ]
            )
            ->joinLeft(
                [$product],
                $product.".".\Ced\Amazon\Model\Product::COLUMN_RELATION_ID.'='.$alias.'.id',
                [
                    'ced_marketplace_id'=> new \Zend_Db_Expr(
                        "GROUP_CONCAT(`{$product}`.marketplace_id ORDER BY `{$product}`.id ASC SEPARATOR '".
                        \Ced\Amazon\Api\Data\ProductInterface::VALUE_SEPARATOR."')"
                    ),
                    'ced_amazon_asin' =>
                        new \Zend_Db_Expr(
                            "GROUP_CONCAT(`{$product}`.asin ORDER BY `{$product}`.id ASC SEPARATOR '".
                            \Ced\Amazon\Api\Data\ProductInterface::VALUE_SEPARATOR."')"
                        ),
                ]
            )->group('e.entity_id');

        /*
         * SELECT product_id, GROUP_CONCAT(profile_id ORDER BY profile_id ASC SEPARATOR ',') AS profile_id
         * FROM ced_amazon_profile_product
         * GROUP BY product_id
         */

        /**
         * SELECT `e`.*, `amazon`.`profile_id` AS `capp_profile_id` FROM `catalog_product_entity` AS `e`
         * INNER JOIN `ced_amazon_profile_product` AS `amazon` ON product_id=entity_id WHERE (profile_id > 0)
         */

    }
}
