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
 * @package     Ced_GXpress
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\GXpress\Model;

class Productchange extends \Magento\Framework\Model\AbstractModel
{

    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    const CRON_TYPE_INVENTORY = 'inventory';
    const CRON_TYPE_PRICE = 'price';
    /**
     * @var string
     */
    protected $_eventPrefix = 'gxpress_productchange';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('Ced\GXpress\Model\ResourceModel\Productchange');
    }

    public function deleteFromProductChange($productIds, $type)
    {
        $this->_getResource()->deleteFromProductChange($productIds, $type);
        return $this;
    }

    public function setProductChange($productId, $oldValue='', $newValue='', $type){
        if ($productId <= 0) {
            return $this;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var \Ced\GXpress\Helper\MultiAccount $multiAccountHelper
         */
        $multiAccountHelper = $objectManager->create('\Ced\GXpress\Helper\MultiAccount');

        $isGXpressProduct = '';
        $parentFound = false;
        $profileAttrs = $multiAccountHelper->getAllProfileAttr();

        $product = $objectManager->create('\Magento\Catalog\Model\Product')->load($productId);

        foreach ($profileAttrs as $profileAttrCode) {
            $isGXpressProduct = $product->getData($profileAttrCode);
            if($isGXpressProduct != '') {
                break;
            }
        }
        $checkForChild = $objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')->getParentIdsByChild($product->getId());
        if($isGXpressProduct == null && count($checkForChild) > 0) {
            foreach ($checkForChild as $childParentId) {
                $product = $objectManager->create('\Magento\Catalog\Model\Product')
                    ->load($childParentId);
                foreach ($profileAttrs as $profileAttrCode) {
                    $isGXpressProduct = $product->getData($profileAttrCode);
                    if($isGXpressProduct != '') {
                        $parentFound = true;
                        break;
                    }
                }
                if($parentFound) {
                    break;
                }
            }
        }


        if ($product && $isGXpressProduct != '') {
            $collection = $this->getCollection()->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('cron_type', $type);

            if (count($collection) > 0) {
                $this->load($collection->getFirstItem()->getId());
                if($oldValue == '') {
                    $oldValue = $collection->getFirstItem()->getOldValue();
                }
            } else {
                $this->setProductId($productId);
            }

            $this->setOldValue($oldValue);
            $this->setNewValue($newValue);
            $this->setAction(self::ACTION_UPDATE);
            $this->setCronType($type);
            $this->save();
        }
        return $this;
    }
}