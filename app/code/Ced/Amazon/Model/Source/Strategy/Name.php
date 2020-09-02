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

use Magento\Framework\Option\ArrayInterface;

class Name implements ArrayInterface
{
    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
    public $productCollectionFactory;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /*
     * Option getter
     * @return array
     */
    public function toOptionArray()
    {
        $vendors = $this->toArray();
        $result = [];

        foreach ($vendors as $key => $value) {
            $result[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return $result;
    }

    /*
     * Get options in "key-value" format
     * @return array
     */
    public function toArray()
    {

        $skus = $this->productCollectionFactory->create()
            ->getColumnValues('sku');

        $vendors = [];
        foreach ($skus as $sku) {
            $skuParts = explode('-', $sku);
            if (is_array($skuParts) && count($skuParts) > 1 && $skuParts[0]) {
                $vendor = $skuParts[0];
            } else {
                $zero = strpos($sku, '0');
                $vendor = substr($sku, 0, $zero);
            }

            $vendors[$vendor] = $vendor;
        }

        return $vendors;
    }
}
