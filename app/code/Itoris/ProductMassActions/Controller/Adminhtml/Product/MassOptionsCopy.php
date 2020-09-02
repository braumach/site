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

class MassOptionsCopy extends \Magento\Backend\App\Action
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
        $fromProductId = $_fromProductId = (int)$this->getRequest()->getParam('from_product_id');
        
        if (!$fromProductId) {
            $this->messageManager->addError(__('Please specify the product ID to copy from'));
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/*/index');
        }
        
        if (is_array($productIds)) {
            //implementing a faster method of copying options through direct SQL
            $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $con = $res->getConnection('write');
            
            if ($this->getDataHelper()->_productIndexColumn == 'row_id') { //Magento EE
                $fromProductId = (int) $con->fetchOne("select `row_id` from {$res->getTableName('catalog_product_entity')} where `entity_id`={$fromProductId}");
            }
            
            $product = $con->fetchRow("select * from {$res->getTableName('catalog_product_entity')} where `{$this->getDataHelper()->_productIndexColumn}`={$fromProductId}");
            $_options = $con->fetchAll("select * from {$res->getTableName('catalog_product_option')} where `product_id`={$fromProductId} order by `option_id`");
            
            if (empty($_options)) {
                $this->messageManager->addError(__(sprintf('Sorry, the product with ID %s has no options', $_fromProductId)));
                $this->_redirect($this->_redirect->getRefererUrl());
                return;
            }
            
            //make a template of options
            $options = [];
            foreach($_options as $_option) {
                $option = ['option' => $_option];
                $option['option_price'] = $con->fetchAll("select * from {$res->getTableName('catalog_product_option_price')} where `option_id`={$_option['option_id']} order by `option_price_id`");
                $option['option_title'] = $con->fetchAll("select * from {$res->getTableName('catalog_product_option_title')} where `option_id`={$_option['option_id']} order by `option_title_id`");
                
                $_items = $con->fetchAll("select * from {$res->getTableName('catalog_product_option_type_value')} where `option_id`={$_option['option_id']} order by `option_type_id`");
                $items = [];
                foreach($_items as $_item) {
                    $item = ['item' => $_item];
                    $item['item_price'] = $con->fetchAll("select * from {$res->getTableName('catalog_product_option_type_price')} where `option_type_id`={$_item['option_type_id']} order by `option_type_price_id`");
                    $item['item_title'] = $con->fetchAll("select * from {$res->getTableName('catalog_product_option_type_title')} where `option_type_id`={$_item['option_type_id']} order by `option_type_title_id`");
                    
                    //make a clean up of IDs
                    unset($item['item']['option_type_id']);
                    foreach($item['item_price'] as $key => $value) unset($item['item_price'][$key]['option_type_price_id']);
                    foreach($item['item_title'] as $key => $value) unset($item['item_title'][$key]['option_type_title_id']);
                    $items[] = $item;
                }
                $option['items'] = $items;
                
                //make a clean up of IDs
                unset($option['option']['option_id']);
                foreach($option['option_price'] as $key => $value) unset($option['option_price'][$key]['option_price_id']);
                foreach($option['option_title'] as $key => $value) unset($option['option_title'][$key]['option_title_id']);
                $options[] = $option;
            }
            
            //applying to each selected product 
            foreach($productIds as $productId) {
                
                if ($this->getDataHelper()->_productIndexColumn == 'row_id') { //Magento EE
                    $productId = (int) $con->fetchOne("select `row_id` from {$res->getTableName('catalog_product_entity')} where `entity_id`={$productId}");
                }

                if ($productId == $fromProductId) continue;
                
                //first remove old options if method is "replace"
                if ($method == 'replace') $con->query("delete from {$res->getTableName('catalog_product_option')} where `product_id`={$productId}");
                
                //then apply the template
                $order = (int) $con->fetchOne("select max(`sort_order`) from {$res->getTableName('catalog_product_option')} where `product_id`={$productId}");
                foreach($options as $option) {
                    $order++;
                    $option['option']['product_id'] = $productId;
                    $option['option']['sort_order'] = $order;
                    $con->query("insert into {$res->getTableName('catalog_product_option')} set ".$this->getDataHelper()->getSqlString($con, $option['option']));
                    $optionId = $con->fetchOne("select max(`option_id`) from {$res->getTableName('catalog_product_option')}");
                    
                    foreach($option['option_price'] as $price) {
                        $price['option_id'] = $optionId;
                        $con->query("insert into {$res->getTableName('catalog_product_option_price')} set ".$this->getDataHelper()->getSqlString($con, $price));
                    }
                    foreach($option['option_title'] as $title) {
                        $title['option_id'] = $optionId;
                        $con->query("insert into {$res->getTableName('catalog_product_option_title')} set ".$this->getDataHelper()->getSqlString($con, $title));
                    }
                    
                    foreach($option['items'] as $item) {
                        $item['item']['option_id'] = $optionId;
                        $con->query("insert into {$res->getTableName('catalog_product_option_type_value')} set ".$this->getDataHelper()->getSqlString($con, $item['item']));
                        $optionTypeId = $con->fetchOne("select max(`option_type_id`) from {$res->getTableName('catalog_product_option_type_value')}");
                        
                        foreach($item['item_price'] as $price) {
                            $price['option_type_id'] = $optionTypeId;
                            $con->query("insert into {$res->getTableName('catalog_product_option_type_price')} set ".$this->getDataHelper()->getSqlString($con, $price));
                        }
                        foreach($item['item_title'] as $title) {
                            $title['option_type_id'] = $optionTypeId;
                            $con->query("insert into {$res->getTableName('catalog_product_option_type_title')} set ".$this->getDataHelper()->getSqlString($con, $title));
                        }
                    }
                }
                
                //updating related information
                $con->query("update {$res->getTableName('catalog_product_entity')} set `has_options`={$product['has_options']}, `required_options`={$product['required_options']} where `{$this->getDataHelper()->_productIndexColumn}`={$productId}");

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