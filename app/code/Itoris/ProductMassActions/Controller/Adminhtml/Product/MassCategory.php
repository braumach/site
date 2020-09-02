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

class MassCategory extends \Magento\Backend\App\Action
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
        $method = $this->getRequest()->getParam('method');
        $catIds = $this->getRequest()->getParam('catid');
        
        if (!$catIds) {
            $this->messageManager->addError(__('Please select a category'));
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/*/index');
        }
        $catIds = explode(',', $catIds);
        
        if (is_array($productIds)) {
            $_stores = $this->_objectManager->create('\Magento\Store\Model\StoreManager')->getStores();
            $stores = [];
            foreach($_stores as $store) $stores[] = $store->getId();            
            
            //implementing a faster method of copying categories through direct SQL
            $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $con = $res->getConnection('write');

            $topLevelCats = $con->fetchCol("select `{$this->getDataHelper()->_productIndexColumn}` from {$res->getTableName('catalog_category_entity')} where `{$this->getDataHelper()->_productIndexColumn}` in (".implode(',', $catIds).") and `level` < 3");
            
            //applying to each selected product 
            foreach($productIds as $productId) {
                $prevCats = [];
                if ($method == 'append') {
                    $_prevCats = $con->fetchAll("select `category_id`, `position` from {$res->getTableName('catalog_category_product')} where `product_id`={$productId}");
                    foreach($_prevCats as $prevCat) $prevCats[$prevCat['category_id']] = $prevCat['position'];
                }

                $con->query("delete from {$res->getTableName('catalog_category_product')} where `product_id`={$productId}".(!in_array(-1, $catIds) && $method == 'remove' ? " and `category_id` in (".implode(',', $catIds).")" : ""));
                $con->query("delete from {$res->getTableName('catalog_category_product_index')} where `product_id`={$productId}".(!in_array(-1, $catIds) && $method == 'remove' ? " and `category_id` in (".implode(',', $catIds).")" : ""));
                if ($method == 'append' || $method == 'replace') {
                    foreach(array_unique(array_merge(array_keys($prevCats), $catIds)) as $catId) {
                        $position = isset($prevCats[$catId]) ? $prevCats[$catId] : null;
                        if (is_null($position)) $position = in_array($catId, $topLevelCats) ? 1 : 10001;
                        $con->query("insert into {$res->getTableName('catalog_category_product')} set `category_id`={$catId}, `product_id`={$productId}, `position`={$position}");
                        foreach($stores as $store) {
                            $con->query("insert into {$res->getTableName('catalog_category_product_index')} set `category_id`={$catId}, `product_id`={$productId}, ".(in_array($catId, $topLevelCats) ? "`position`={$position}, `is_parent`=1" : "`position`={$position}, `is_parent`=0").", store_id={$store}, `visibility`=4");
                        }
                    }
                }
            }
            $this->messageManager->addSuccess(sprintf(__('%s products have been changed'), count($productIds)));
            
            //invalidate FPC
            $cacheTypeList = $this->_objectManager->create('\Magento\Framework\App\Cache\TypeList');
            //$cacheTypeList->invalidate(['block_html', 'full_page']);
            $cacheTypeList->cleanType('block_html');
            $cacheTypeList->cleanType('full_page');
        } else {
            $this->messageManager->addError(__('Please select product ids'));
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/*/index');
    }
    
    protected function getDataHelper() {
        return $this->_objectManager->get('Itoris\ProductMassActions\Helper\Data');
    }
}