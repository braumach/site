<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PRODUCT_MASS_ACTIONS
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\ProductMassActions\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;

class MassAttribute extends \Magento\Backend\App\Action
{
    /**
     * @return mixed
     */
    public function execute()
    {
        $filter = $this->_objectManager->create('Magento\Ui\Component\MassAction\Filter');
        $collectionFactory = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $collection = $filter->getCollection($collectionFactory->create());
        $productIds = $collection->getAllIds();
        $attribute = $this->getRequest()->getParam('massAttribute', '');
        $value = $this->getRequest()->getParam('massAttributeValue', '');
        $valueType = (int)$this->getRequest()->getParam('massAttributeValueType', 0);
        $valueBase = $this->getRequest()->getParam('massAttributeValueBase', '');
        $method = (int)$this->getRequest()->getParam('massAttributeMethod', 0);
        $storeId = (int)$this->getRequest()->getParam('storeId', 0);           

        $attributeBase = $this->_objectManager->create('\Magento\Eav\Model\Config')->getAttribute('catalog_product', $valueBase && $valueBase != $attribute ? $valueBase : $attribute);

        if ($attribute != 'qty' && $attribute != 'stock_status') {
            $attribute = $this->_objectManager->create('\Magento\Eav\Model\Config')->getAttribute('catalog_product', $attribute);
            
            if (!$attribute->getId()) {
                $this->messageManager->addError(__('Attribute not found'));
                return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/*/index');
            }
        }
        
        if (is_array($productIds)) {
            //implementing a faster method of copying cross-sells through direct SQL
            $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $con = $res->getConnection('write');
            
            if ($attribute == 'qty') {
                //applying to each selected product 
                foreach($productIds as $productId) {
                    $prevValue = (float)$con->fetchOne("select `qty` from {$res->getTableName('cataloginventory_stock_item')} where `product_id`={$productId}");
                    $newValue = $method == 1 ? $prevValue + (float)$value : ( $method == 2 ? $prevValue - (float)$value : (float)$value );
                    $con->query("update {$res->getTableName('cataloginventory_stock_item')} set `qty`=".$con->quote($newValue)." where `product_id`={$productId}");
                    $con->query("update {$res->getTableName('cataloginventory_stock_status')} set `qty`=".$con->quote($newValue)." where `product_id`={$productId}");
                    $con->query("update {$res->getTableName('cataloginventory_stock_status_idx')} set `qty`=".$con->quote($newValue)." where `product_id`={$productId}");
                }
            } else if ($attribute == 'stock_status') {
                //applying to each selected product
                $value = (int)$value ? 1 : 0;
                foreach($productIds as $productId) {
                    $con->query("update {$res->getTableName('cataloginventory_stock_item')} set `is_in_stock`=".$con->quote($value)." where `product_id`={$productId}");
                    $con->query("update {$res->getTableName('cataloginventory_stock_status')} set `stock_status`=".$con->quote($value)." where `product_id`={$productId}");
                    $con->query("update {$res->getTableName('cataloginventory_stock_status_idx')} set `stock_status`=".$con->quote($value)." where `product_id`={$productId}");
                }            
            } else {            
                $db_name = $res->getTableName('catalog_product_entity_'.$attribute->getBackendType());
                $db_name_base = $res->getTableName('catalog_product_entity_'.$attributeBase->getBackendType());
                //applying to each selected product 
                foreach($productIds as $productId) {
                    if ($this->getDataHelper()->_productIndexColumn == 'row_id') { //Magento EE
                        $productId = (int) $con->fetchOne("select `row_id` from {$res->getTableName('catalog_product_entity')} where `entity_id`={$productId}");
                    }
                    $_prevValue = (array)$con->fetchRow("select `value_id`, `value` from {$db_name} where `attribute_id`={$attribute->getId()} and `store_id`={$storeId} and `{$this->getDataHelper()->_productIndexColumn}`={$productId}");
                    $prevValue = $con->fetchRow("select `value_id`, `value` from {$db_name_base} where `attribute_id`={$attributeBase->getId()} and `store_id`={$storeId} and `{$this->getDataHelper()->_productIndexColumn}`={$productId}");
                    $newValue = $method == 0 ? $value : ($method == 1 ? (float)$prevValue['value'] + ($valueType ? floatval($prevValue['value']) / 100 * $value : $value) : (float) $prevValue['value'] - ($valueType ? floatval($prevValue['value']) / 100 * $value : $value));
                    if ($method > 0 && $attribute->getFrontendInput() == 'multiselect') {
                        $_prevValue['value'] = $prevValue['value'] == '' ? [] : explode(',', $prevValue['value']);
                        $_value = explode(',', $value);
                        if ($method == 1) $newValue = implode(',', array_unique(array_merge($_prevValue['value'], $_value)));
                            else $newValue = implode(',', array_unique(array_diff($_prevValue['value'], $_value)));
                    }
                    if (isset($_prevValue['value_id']) && (int) $_prevValue['value_id']) $con->query("update {$db_name} set `value`=".$con->quote($newValue)." where `attribute_id`={$attribute->getId()} and `store_id`={$storeId} and `{$this->getDataHelper()->_productIndexColumn}`={$productId}");
                        else $con->query("insert into {$db_name} set `value`=".$con->quote($newValue).", `attribute_id`={$attribute->getId()}, `store_id`={$storeId}, `{$this->getDataHelper()->_productIndexColumn}`={$productId}");
                }
            }
            $this->messageManager->addSuccess(sprintf(__('%s products have been changed'), count($productIds)));
            
            //invalidate FPC
            $cacheTypeList = $this->_objectManager->create('\Magento\Framework\App\Cache\TypeList');
            $cacheTypeList->invalidate('full_page');
        } else {
            $this->messageManager->addError(__('Please select product ids'));
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/*/index');
    }
    
    protected function getDataHelper() {
        return $this->_objectManager->get('Itoris\ProductMassActions\Helper\Data');
    }
}