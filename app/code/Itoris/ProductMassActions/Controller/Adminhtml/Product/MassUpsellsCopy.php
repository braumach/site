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

use \Magento\Catalog\Model\Product\Link;
use Magento\Framework\Controller\ResultFactory;

class MassUpsellsCopy extends \Magento\Backend\App\Action
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
        $fromProductId = (int)$this->getRequest()->getParam('from_product_id');
        
        if (!$fromProductId) {
            $this->messageManager->addError(__('Please specify the product ID to copy from'));
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/*/index');
        }
        
        if (is_array($productIds)) {
            //implementing a faster method of copying upsells through direct SQL
            $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $con = $res->getConnection('write');

            $relations = $con->fetchAll("select * from {$res->getTableName('catalog_product_link')} where `product_id`={$fromProductId} and `link_type_id`=".Link::LINK_TYPE_UPSELL." order by `link_id`");
            
            if (empty($relations)) {
                $this->messageManager->addError(sprintf(__('Sorry, the product with ID %s has no upsell products'), $fromProductId));
                $this->_redirect($this->_redirect->getRefererUrl());
                return;
            }
            
            //applying to each selected product 
            foreach($productIds as $productId) {
                if ($productId == $fromProductId) continue;
                
                //first remove old relations if method is "replace"
                if ($method == 'replace') $con->query("delete from {$res->getTableName('catalog_product_link')} where `product_id`={$productId} and `link_type_id`=".Link::LINK_TYPE_UPSELL." order by `link_id`");

                //then apply the template
                foreach($relations as $relation) {
                    unset($relation['link_id']);
                    $relation['product_id'] = $productId;
                    
                    //making sure the link does not yet exist and skip adding if so
                    $link_id = (int) $con->fetchOne("select `link_id` from {$res->getTableName('catalog_product_link')} where ".str_replace(',',' AND ',$this->getDataHelper()->getSqlString($con, $relation)));
                    if (!$link_id) $con->query("insert into {$res->getTableName('catalog_product_link')} set ".$this->getDataHelper()->getSqlString($con, $relation));
                }
                
                //updating positions
                $links = (array)$con->fetchCol("select `link_id` from {$res->getTableName('catalog_product_link')} where `product_id`={$productId} and `link_type_id`=".Link::LINK_TYPE_UPSELL);
                if (count($links)) {
                    $positionAttributeId = (int)$con->fetchOne("select `product_link_attribute_id` from {$res->getTableName('catalog_product_link_attribute')} where `product_link_attribute_code`='position' and `link_type_id`=".Link::LINK_TYPE_UPSELL);
                    $con->query("delete from {$res->getTableName('catalog_product_link_attribute_int')} where `link_id` in (".implode(',', $links).")");
                    foreach($links as $key => $linkId) $con->query("insert into {$res->getTableName('catalog_product_link_attribute_int')} set `product_link_attribute_id`={$positionAttributeId}, `value`=".($key+1).", `link_id`=".$linkId);
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