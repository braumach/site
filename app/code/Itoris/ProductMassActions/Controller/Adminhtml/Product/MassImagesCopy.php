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

class MassImagesCopy extends \Magento\Backend\App\Action
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

            //implementing a faster method of copying cross-sells through direct SQL
            $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $con = $res->getConnection('write');
            
            if ($this->getDataHelper()->_productIndexColumn == 'row_id') { //Magento EE
                $fromProductId = (int) $con->fetchOne("select `row_id` from {$res->getTableName('catalog_product_entity')} where `entity_id`={$fromProductId}");
            }

            //making a snapshot of images
            $baseDir = substr(__FILE__,0,strpos(strtolower(__FILE__),'app')-1);
            $images = $con->fetchAll("select * from {$res->getTableName('catalog_product_entity_media_gallery_value')} where `{$this->getDataHelper()->_productIndexColumn}`={$fromProductId} order by `position`");
            $gallery = []; $md5s = [];

            foreach($images as $key => $image) {
                unset($images[$key]['record_id']);
                $gallery[$key] = $con->fetchRow("select * from {$res->getTableName('catalog_product_entity_media_gallery')} where `value_id`=".$image['value_id']);
                unset($gallery[$key]['value_id']);
                $imagePath = $baseDir. DS. 'pub'. DS. 'media'. DS.'catalog'. DS. 'product'. $gallery[$key]['value'];
                $md5s[$key] = file_exists($imagePath) ? md5_file($imagePath) : '';
            }
            $attribIds = $con->fetchCol("select `attribute_id` from {$res->getTableName('eav_attribute')} where `frontend_input` = 'media_image'");
            $varcharAttributesToCopy = $con->fetchAll("select * from {$res->getTableName('catalog_product_entity_varchar')} where `{$this->getDataHelper()->_productIndexColumn}`={$fromProductId} and `attribute_id` in (".implode(',', $attribIds).")");
            
            if (empty($images)) {
                $this->messageManager->addError(sprintf(__('Sorry, the product with ID %s has no images'), $_fromProductId));
                $this->_redirect($this->_redirect->getRefererUrl());
                return;
            }
            
            //applying to each selected product 
            foreach($productIds as $productId) {
                if ($this->getDataHelper()->_productIndexColumn == 'row_id') { //Magento EE
                    $productId = (int) $con->fetchOne("select `row_id` from {$res->getTableName('catalog_product_entity')} where `entity_id`={$productId}");
                }

                if ($productId == $fromProductId) continue;
                
                //first remove old images if method is "replace"
                if ($method == 'replace') {
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

                //then apply the template
                $_varcharAttributesToCopy = $varcharAttributesToCopy;
                foreach($images as $key => $image) {
                    $position = (int) $con->fetchOne("select max(`position`) from {$res->getTableName('catalog_product_entity_media_gallery_value')}");
                    $image['position'] = $position + 1;
                        
                    $fromImagePath = $baseDir. DS. 'pub'. DS. 'media'. DS.'catalog'. DS. 'product'. $gallery[$key]['value'];
                    $fileExt = explode('.', $gallery[$key]['value']);
                    $fileExt = end($fileExt);
                    
                    $sku = $con->fetchOne("select `sku` from {$res->getTableName('catalog_product_entity')} where `{$this->getDataHelper()->_productIndexColumn}`={$productId}");
                    $toFileName = strtolower(preg_replace('/[^a-z0-9\._-]+/i', '_', $sku.' '.$image['position'].' '.$image['label']));
                    $toImageDir = $baseDir. DS. 'pub'. DS. 'media'. DS.'catalog'. DS. 'product'. DS. $toFileName[0]. DS. $toFileName[1] . DS;
                    $toImagePath = $toImageDir.$toFileName.'.'.$fileExt;
                    if (!file_exists($toImageDir)) @mkdir($toImageDir, 0777, true);
                    if (!file_exists($toImagePath)) @copy($fromImagePath, $toImagePath);
                    
                    $_gallery = $gallery[$key];
                    $_gallery['value'] = '/'.$toFileName[0].'/'.$toFileName[1].'/'.$toFileName.'.'.$fileExt;
                    $con->query("insert into {$res->getTableName('catalog_product_entity_media_gallery')} set ".$this->getDataHelper()->getSqlString($con, $_gallery));
                    $valueId = (int) $con->fetchOne("select max(`value_id`) from {$res->getTableName('catalog_product_entity_media_gallery')}");
                    
                    $image[$this->getDataHelper()->_productIndexColumn] = $productId;
                    $image['value_id'] = $valueId;
                    $con->query("insert into {$res->getTableName('catalog_product_entity_media_gallery_value')} set ".$this->getDataHelper()->getSqlString($con, $image));
                    if (!(int)$image['store_id']) $con->query("insert into {$res->getTableName('catalog_product_entity_media_gallery_value_to_entity')} set `value_id`={$valueId}, `{$this->getDataHelper()->_productIndexColumn}`={$productId}");

                    foreach($_varcharAttributesToCopy as $_key => $_value) if ($_value['value'] == $gallery[$key]['value']) $_varcharAttributesToCopy[$_key]['value'] = $_gallery['value'];
                }

                if ($method == 'replace') {
                    foreach($_varcharAttributesToCopy as $_key => $_value) {
                        unset($_value['value_id']);
                        $_value[$this->getDataHelper()->_productIndexColumn] = $productId;
                        $con->query("delete from {$res->getTableName('catalog_product_entity_varchar')} where `{$this->getDataHelper()->_productIndexColumn}` = {$productId} and `attribute_id`= {$_value['attribute_id']} and `store_id`={$_value['store_id']}");                        
                        $con->query("insert into {$res->getTableName('catalog_product_entity_varchar')} set ".$this->getDataHelper()->getSqlString($con, $_value));                        
                    }
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