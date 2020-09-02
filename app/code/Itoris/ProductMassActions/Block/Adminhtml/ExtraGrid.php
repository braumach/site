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

namespace Itoris\ProductMassActions\Block\Adminhtml;

use Magento\Framework\App\ResourceConnection;

class ExtraGrid extends \Magento\Catalog\Block\Product\View\Options
{
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\Product\Option $option,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Catalog\Model\Category $categoryManager,
        \Magento\Eav\Model\Config $attributeConfig,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        parent::__construct($context, $pricingHelper, $catalogData, $jsonEncoder, $option, $registry, $arrayUtils, $data);
        $this->_storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $this->_categoryManager = $categoryManager;
        $this->_attributeConfig = $attributeConfig;
    }
    
    public function getCategories() {
        $this->_dbresource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $this->_dbconnection = $this->_dbresource->getConnection('read');
        $this->_catalog_category_entity = $this->_dbresource->getTableName('catalog_category_entity');
        $this->_catalog_category_entity_varchar = $this->_dbresource->getTableName('catalog_category_entity_varchar');
        $rootCatId = (int)$this->_dbconnection->fetchOne("select `entity_id` from {$this->_catalog_category_entity} where `level`=0");
        $rootCategory = $this->_categoryManager->load($rootCatId);
        $entityTypeId = $rootCategory->getResource()->getEntityType()->getId();
        $this->_name_attribute = $this->_dbconnection->fetchOne("select `attribute_id` from {$this->_dbresource->getTableName('eav_attribute')} where `attribute_code`='name' and `entity_type_id`={$entityTypeId}");
        //$categories = [['id' => $rootCatId, 'name' => $rootCategory->getName(), 'level' => 1]];
        $this->getChildCategories($rootCatId, $categories);
        return $categories;
    } 
    
    public function getChildCategories($categoryId, & $categories) {
        $subCategories = $this->_dbconnection->fetchAll("select * from {$this->_catalog_category_entity} where `parent_id`={$categoryId} order by `position` asc");
        foreach($subCategories as $subCategory) {
            $name = $this->_dbconnection->fetchOne("select `value` from {$this->_catalog_category_entity_varchar} where `{$this->getDataHelper()->_productIndexColumn}`={$subCategory[$this->getDataHelper()->_productIndexColumn]} and `attribute_id`={$this->_name_attribute} and (`store_id`={$this->_storeManager->getStore()->getId()} or `store_id`=0)");
            $categories[] = ['id' => $subCategory['entity_id'], 'name' => $name, 'level' => (int) $subCategory['level']];
            $this->getChildCategories($subCategory['entity_id'], $categories);
        }
    }
    
    public function getAttributeSets() {
        $this->_dbresource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $this->_dbconnection = $this->_dbresource->getConnection('read');
        $entityTypeId = (int)$this->_dbconnection->fetchOne("select `entity_type_id` from {$this->_dbresource->getTableName('eav_entity_type')} where `entity_type_code`='catalog_product'");
        return $this->_dbconnection->fetchAll("select `attribute_set_id` as `id`, `attribute_set_name` as `name` from {$this->_dbresource->getTableName('eav_attribute_set')} where `entity_type_id`={$entityTypeId} order by `sort_order` ASC, `attribute_set_name` ASC");
    }
    
    public function getAttributes(){
        $attributeCollection = $this->_attributeConfig->getEntityType('catalog_product')->getAttributeCollection();
        $attributeCollection->addFieldToFilter('frontend_input', ['nin' => ['media_image', 'gallery']]);
        $attributeCollection->addFieldToFilter('backend_type', ['nin' => 'static']);
        $attributeCollection->setOrder('frontend_label', 'ASC');
        $attributes = [
            [   'id' => 0,
                'name' => __('Properties'),
                'grouplabel' => true
            ],
            [   'id' => 0.01,
                'code' => 'qty',
                'name' => __('QTY (Quantity)'),
                'input' => 'int',
                'type' => 'int',
                'is_required' => 1,
                'options' => []
            ],
            [   'id' => 0.02,
                'code' => 'stock_status',
                'name' => __('Stock Status'),
                'input' => 'boolean',
                'type' => 'select',
                'is_required' => 1,
                'options' => [['value' => 1, 'label' => __('In Stock')], ['value' => 0, 'label' =>__('Out of Stock')]]
            ],
            [   'id' => 0.03,
                'code' => 'sku',
                'name' => __('SKU'),
                'input' => 'text',
                'type' => 'text',
                'is_required' => 1
            ],
            [   'id' => 0.99,
                'name' => __('Attributes'),
                'grouplabel' => true
            ]
        ];
        foreach($attributeCollection as $attribute) {
			if ($attribute->getAttributeCode() == 'quantity_and_stock_status') continue;
            $_options = $attribute->getOptions();
            $options = [];
            foreach($_options as $option) {
                $options[] = [
                    'value' => $option->getValue(),
                    'label' => $option->getLabel(),
                    'is_default' => (int) $option->getIsDefault() || $option->getValue() == $attribute->getDefaultValue()
                ];
            }
            $attributes[] = [
                'id' => $attribute->getId(),
                'code' => $attribute->getAttributeCode(),
                'name' => $attribute->getFrontendLabel(),
                'input' => $attribute->getFrontendInput(),
                'type' => $attribute->getBackendType(),
                'is_required' => (int) $attribute->getIsRequired(),
                'options' => $options
            ];
        }
        return $attributes;
    }
    
    public function isEnabled() {
        return $this->getDataHelper()->isEnabled();
    }
    
    protected function getDataHelper() {
        return $this->_objectManager->get('Itoris\ProductMassActions\Helper\Data');
    }
}