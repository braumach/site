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

defined( 'DS' ) or define( 'DS', DIRECTORY_SEPARATOR );

class MassImagesRemove extends \Magento\Backend\App\Action
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
        
        if (is_array($productIds)) {
            //implementing a faster method of removing images through direct SQL
            $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $con = $res->getConnection('write');
            
            $baseDir = substr(__FILE__,0,strpos(strtolower(__FILE__),'app')-1);
            $attribIds = $con->fetchCol("select `attribute_id` from {$res->getTableName('eav_attribute')} where `frontend_input` = 'media_image'");
            
            foreach($productIds as $productId) {
                if ($this->getDataHelper()->_productIndexColumn == 'row_id') { //Magento EE
                    $productId = (int) $con->fetchOne("select `row_id` from {$res->getTableName('catalog_product_entity')} where `entity_id`={$productId}");
                }

                $con->query("delete from {$res->getTableName('catalog_product_entity_media_gallery_value_to_entity')} where `{$this->getDataHelper()->_productIndexColumn}`={$productId}");
                $_images = $con->fetchAll("select * from {$res->getTableName('catalog_product_entity_media_gallery_value')} where `{$this->getDataHelper()->_productIndexColumn}`={$productId} order by `position`");
                foreach($_images as $_image) {
                    $value = $con->fetchOne("select `value` from {$res->getTableName('catalog_product_entity_media_gallery')} where `value_id`=".$_image['value_id']);
                    $con->query("delete from {$res->getTableName('catalog_product_entity_media_gallery_value')} where `value_id`=".$_image['value_id']);                        
                    $con->query("delete from {$res->getTableName('catalog_product_entity_media_gallery')} where `value_id`=".$_image['value_id']);
                    $_imagePath = $baseDir. DS. 'pub'. DS. 'media'. DS.'catalog'. DS. 'product'. $value;
                    if (file_exists($_imagePath)) @unlink($_imagePath);
                    
                    $con->query("delete from {$res->getTableName('catalog_product_entity_varchar')} where `{$this->getDataHelper()->_productIndexColumn}`={$productId} and `attribute_id` in (".implode(',', $attribIds).")");                        
                }
            }
            
            try {
                $this->_objectManager->create('Magento\Catalog\Model\Product\Image')->clearCache();
                $this->_eventManager->dispatch('clean_catalog_images_cache_after');
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('An error occurred while clearing the image cache.'));
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