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

class MassRelationsRemove extends \Magento\Backend\App\Action
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
            //implementing a faster method of removing relations through direct SQL
            $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $con = $res->getConnection('write');
            
            $con->query("delete from {$res->getTableName('catalog_product_link')} where `product_id` in (".implode(',',$productIds).") and `link_type_id`=".Link::LINK_TYPE_RELATED." order by `link_id`");
            
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