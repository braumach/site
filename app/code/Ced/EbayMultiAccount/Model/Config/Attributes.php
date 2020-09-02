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

namespace Ced\EbayMultiAccount\Model\Config;

class Attributes implements \Magento\Framework\Option\ArrayInterface
{

    protected $attributes;

    /**
     * Attributes constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributes
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributes
    )
    {
        $this->attributes = $attributes;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $attributes = $this->attributes->getItems();

        $magentoattributeCodeArray= [];
        $magentoattributeCodeArray[]= ['label' => 'Please Select', 'value' => ''];

        foreach ($attributes as $attribute){
            $magentoattributeCodeArray[] = [
                'label' => $attribute->getFrontendLabel(),
                'value' => $attribute->getAttributecode()];
        }

        return $magentoattributeCodeArray;


    }
}
