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
 * @package     Ced_TradeMe
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\TradeMe\Model;

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
    protected $_eventPrefix = 'trademe_product_change';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('Ced\TradeMe\Model\ResourceModel\Productchange');
    }

    public function deleteFromProductChange($productIds, $type, $accountId)
    {
        $this->_getResource()->deleteFromProductChange($productIds, $type, $accountId);
        return $this;
    }

    public function setProductChange($productId, $oldValue='', $newValue='', $type, $accountId){
        if ($productId <= 0) {
            return $this;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var \Ced\TradeMe\Helper\MultiAccount $multiAccountHelper
         */
        $multiAccountHelper = $objectManager->create('\Ced\TradeMe\Helper\MultiAccount');

        $isTradeMeProduct = '';
        $parentFound = false;
        $profileAttrs = $multiAccountHelper->getAllProfileAttr();

        $product = $objectManager->create('\Magento\Catalog\Model\Product')->load($productId);

        foreach ($profileAttrs as $profileAttrCode) {
            $isTradeMeProduct = $product->getData($profileAttrCode);
            if($isTradeMeProduct != '') {
                break;
            }
        }
        $checkForChild = $objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')->getParentIdsByChild($product->getId());
        if($isTradeMeProduct == null && count($checkForChild) > 0) {
            foreach ($checkForChild as $childParentId) {
                $product = $objectManager->create('\Magento\Catalog\Model\Product')
                    ->load($childParentId);
                foreach ($profileAttrs as $profileAttrCode) {
                    $isTradeMeProduct = $product->getData($profileAttrCode);
                    if($isTradeMeProduct != '') {
                        $parentFound = true;
                        break;
                    }
                }
                if($parentFound) {
                    break;
                }
            }
        }


        if ($product && $isTradeMeProduct != '') {
            $collection = $this->getCollection()->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('cron_type', $type)->addFieldToFilter('account_id', $accountId);

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
            $this->setAccountId($accountId);
            $this->save();
        }
        return $this;
    }
}