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
 * @category  Ced
 * @package   Ced_MPCatch
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\MPCatch\Model\Source\Config;

class MagentoAttributes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Ced\MPCatch\Helper\Category
     * */
    protected $attributeCollection;

    /**
     * @param Category $category
     * */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributeCollection
    )
    {
        $this->attributeCollection = $attributeCollection;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $catchAttributes = [];
        $attributes = $this->attributeCollection;
        foreach ($attributes as $attribute) {
            $catchAttributes[] = array(
                'label' => $attribute->getFrontendLabel(),
                'value' => $attribute->getAttributeCode()
            );
        }
        return $catchAttributes;
    }
}
