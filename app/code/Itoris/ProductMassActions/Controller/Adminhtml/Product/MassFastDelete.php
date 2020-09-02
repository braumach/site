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

class MassFastDelete extends \Magento\Backend\App\Action
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
            //implementing a faster method of deletion through direct SQL
            $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $con = $res->getConnection('write');
			$tables = $con->fetchCol("SHOW TABLES");
			$flatIndexTables = []; $i = 1;
			do {
				$tableFound = false;
				foreach($tables as $table) {
					if (stripos($table, 'catalog_product_flat_'.$i) !== false) {
						$flatIndexTables[] = 'catalog_product_flat_'.$i;
						$tableFound = true;
						$i++;
					}
				}
			} while ($tableFound);
			$fullTextTables = []; $i = 1;
			do {
				$tableFound = false;
				foreach($tables as $table) {
					if (stripos($table, 'catalogsearch_fulltext_scope'.$i) !== false) {
						$fullTextTables[] = 'catalogsearch_fulltext_scope'.$i;
						$tableFound = true;
						$i++;
					}
				}
			} while ($tableFound);
			
			$parts = array_chunk($productIds, 1000);
			foreach($parts as $block) {
				$productsStr = implode(',', $block);
				
				$con->query("delete from {$res->getTableName('catalog_product_entity')} where `entity_id` IN ({$productsStr})");
				$con->query("delete from {$res->getTableName('catalog_category_product_index')} where `product_id` IN ({$productsStr})");
				foreach ($flatIndexTables as $table) $con->query("delete from {$res->getTableName($table)} where `entity_id` IN ({$productsStr})");
				$con->query("delete from {$res->getTableName('catalog_product_index_eav')} where `entity_id` IN ({$productsStr})");
				$con->query("delete from {$res->getTableName('catalog_product_index_eav_idx')} where `entity_id` IN ({$productsStr})");
				$con->query("delete from {$res->getTableName('cataloginventory_stock_status')} where `product_id` IN ({$productsStr})");
				$con->query("delete from {$res->getTableName('cataloginventory_stock_status_idx')} where `product_id` IN ({$productsStr})");
				foreach ($fullTextTables as $table) $con->query("delete from {$res->getTableName($table)} where `entity_id` IN ({$productsStr})");
				$con->query("delete from {$res->getTableName('url_rewrite')} where `entity_id` IN ({$productsStr}) and `entity_type`=".$con->quote('product'));
			}
			
            $this->messageManager->addSuccess(sprintf(__('%s products have been deleted'), count($productIds)));
            
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