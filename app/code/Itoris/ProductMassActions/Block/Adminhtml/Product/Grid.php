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
 
namespace Itoris\ProductMassActions\Block\Adminhtml\Product;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    protected $_objectManager = null;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        parent::__construct($context, $backendHelper, $data);
        $this->_type = $type;
        $this->_setsFactory = $setsFactory;
        $this->_productFactory = $productFactory;
    }

    public function _construct()
    {
        parent::_construct();
        $this->setId('mass_actions_product_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection to be displayed in the grid
     *
     * @return \Magento\Sales\Block\Adminhtml\Order\Create\Search
     */
    protected function _prepareCollection()
    {
        $attributes = $this->_objectManager->get('Magento\Catalog\Model\Config')->getProductAttributes();
        $collection = $this->_objectManager->create('Magento\Catalog\Model\Product')->getCollection();
        $collection
            ->addAttributeToSelect($attributes)
            ->addAttributeToSelect('sku')
            ->addStoreFilter();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn($this->getDataHelper()->_productIndexColumn, [
            'header'    => $this->escapeHtml(__('ID')),
            'sortable'  => true,
            'width'     => '60',
            'index'     => $this->getDataHelper()->_productIndexColumn
        ]);
        $this->addColumn('name', [
            'header'    => $this->escapeHtml(__('Product Name')),
            'index'     => 'name'
        ]);
        $this->addColumn('type',['header' => $this->escapeHtml(__('Type')),
                'index' => 'type_id',
                'type' => 'options',
                'options' => $this->_type->getOptionArray()
        ]);
        
        $sets = $this->_setsFactory->create()->setEntityTypeFilter(
            $this->_productFactory->create()->getResource()->getTypeId()
        )->load()->toOptionHash();

        $this->addColumn(
            'set_name',
            [
                'header' => $this->escapeHtml(__('Attribute Set')),
                'index' => 'attribute_set_id',
                'type' => 'options',
                'options' => $sets,
                'header_css_class' => 'col-attr-name',
                'column_css_class' => 'col-attr-name'
            ]
        );
        
        $this->addColumn('sku', [
            'header'    => $this->escapeHtml(__('SKU')),
            'width'     => '80',
            'index'     => 'sku'
        ]);
        $this->addColumn('price', [
            'header'    => $this->escapeHtml(__('Price')),
            'column_css_class' => 'price',
            'align'     => 'center',
            'type'      => 'currency',
            'currency_code' => $this->getStore()->getCurrentCurrencyCode(),
            'rate'      => $this->getStore()->getBaseCurrency()->getRate($this->getStore()->getCurrentCurrencyCode()),
            'index'     => 'price',
        ]);

        $this->addColumn('add_product', [
            'header'    => $this->escapeHtml(__('Select')),
            'header_css_class' => 'a-center',
            'type'      => 'text',
            'name'      => $this->getDataHelper()->_productIndexColumn,
            'align'     => 'center',
            'index'     => $this->getDataHelper()->_productIndexColumn,
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => 'Itoris\ProductMassActions\Block\Adminhtml\Product\Grid\Column\Link',
        ]);

        return parent::_prepareColumns();
    }

    public function getStore() {
        return $this->_storeManager->getStore($this->getRequest()->getParam('store'));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/productGrid', ['_current' => true, 'collapse' => null]);
    }

    public function getRowClickCallback() {
        return "function() { return false; };";
    }
    
    protected function getDataHelper() {
        return $this->_objectManager->get('Itoris\ProductMassActions\Helper\Data');
    }
}