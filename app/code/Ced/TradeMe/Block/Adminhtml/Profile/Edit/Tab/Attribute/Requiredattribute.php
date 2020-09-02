<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_TradeMe
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\TradeMe\Block\Adminhtml\Profile\Edit\Tab\Attribute;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use \Magento\Backend\Block\Widget;

/**
 * Class Requiredattribute
 * @package Ced\TradeMe\Block\Adminhtml\Profile\Edit\Tab\Attribute
 */
class Requiredattribute extends Widget implements RendererInterface
{

    /**
     * @var string
     */
    protected $_template = 'Ced_TradeMe::profile/attribute/required_attribute.phtml';
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected  $_objectManager;
    /**
     * @var \Magento\Framework\Registry
     */
    protected  $_coreRegistry;
    /**
     * @var mixed
     */
    protected  $_profile;
    /**
     * @var
     */
    protected  $_trademeAttribute;

    /**
     * Requiredattribute constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        array $data = []

    )
    {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $registry;
        $this->_profile = $this->_coreRegistry->registry('current_profile');
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            ['label' => __('Add Attribute'), 'onclick' => 'return requiredAttributeControl.addItem()', 'class' => 'add']
        );
        $button->setName('add_required_item_button');

        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * @return array
     */
    public function getTradeMeAttributes()
    {
        $pickup = $this->_objectManager->get('Ced\TradeMe\Model\Source\Pickup')->getAllOptions();
        $listingDuration = $this->_objectManager->get('Ced\TradeMe\Model\Source\ListingDuration')->getAllOptions();
        $requiredAttribute = [
            'Title' => ['trademe_attribute_name' => 'Title','trademe_attribute_type' => 'text', 'trademe_attribute_enum' => '','magento_attribute_code' => 'name', 'required' => 1],
            'StartPrice' => ['trademe_attribute_name' => 'StartPrice','trademe_attribute_type' => 'text', 'trademe_attribute_enum' => '','magento_attribute_code' => 'price', 'required' => 1],
            'SKU' => ['trademe_attribute_name' => 'SKU','trademe_attribute_type' => 'text', 'trademe_attribute_enum' => '','magento_attribute_code' => 'sku', 'required' => 1],
            'Description' => ['trademe_attribute_name' => 'Description','trademe_attribute_type' => 'textarea', 'trademe_attribute_enum' => '','magento_attribute_code' => 'description', 'required' => 1],
            'Duration' => ['trademe_attribute_name' => 'Duration','trademe_attribute_type' => 'select', 'trademe_attribute_enum' => /*implode(',', $listingDuration)*/ $listingDuration,'magento_attribute_code' => '', 'required' => 1],
            'Pickup' => ['trademe_attribute_name' => 'Pickup','trademe_attribute_type' => 'select', 'trademe_attribute_enum' => /*implode(',', $listingDuration)*/ $pickup,'magento_attribute_code' => '', 'required' => 1],
            'Inventory And Stock' => ['trademe_attribute_name' => 'inventory','trademe_attribute_type' => 'text', 'trademe_attribute_enum' => '','magento_attribute_code' => 'quantity_and_stock_status', 'required' => 1]
        ];
        $optionalAttribues = [
            'UPC' => ['trademe_attribute_name' => 'upc','trademe_attribute_type' => 'text', 'trademe_attribute_enum' => ''],
            'EAN' => ['trademe_attribute_name' => 'ean','trademe_attribute_type' => 'text', 'trademe_attribute_enum' => ''],
            'ISBN' => ['trademe_attribute_name' => 'isbn','trademe_attribute_type' => 'text', 'trademe_attribute_enum' => ''],
            'SubTitle' => ['trademe_attribute_name' => 'SubTitle','trademe_attribute_type' => 'text', 'trademe_attribute_enum' => ''],
            'OriginalRetailPrice' => ['trademe_attribute_name' => 'OriginalRetailPrice','trademe_attribute_type' => 'text', 'trademe_attribute_enum' => ''],
            'BuyItNowPrice' => ['trademe_attribute_name' => 'BuyItNowPrice','trademe_attribute_type' => 'text', 'trademe_attribute_enum' => ''],
            'BestOfferEnabled' => ['trademe_attribute_name' => 'bestofferenabled','trademe_attribute_type' => "boolean", 'trademe_attribute_enum' => 'false,true'],
            'Auto Pay' => ['trademe_attribute_name' => 'auto_pay','trademe_attribute_type' => "boolean", 'trademe_attribute_enum' => 'false,true'],
            'Brand' => ['trademe_attribute_name' => 'brand','trademe_attribute_type' => 'text', 'trademe_attribute_enum' => ''],
            'Manufacturer Part Number' => ['trademe_attribute_name' => 'manufacturer_part_number','trademe_attribute_type' => 'text', 'trademe_attribute_enum' => ''],
            'Bullets' => ['trademe_attribute_name' => 'bullets','trademe_attribute_type' => 'textarea', 'trademe_attribute_enum' => ''],
        ];

        $this->_trademeAttribute[] = array(
            'label' => __('Required Attributes'),
            'value' => $requiredAttribute
        );


        $this->_trademeAttribute[] = array(
            'label' => __('Optional Attributes'),
            'value' => $optionalAttribues
        );
        return $this->_trademeAttribute;
    }

    /**
     * @return mixed
     */
    public function getMagentoAttributes()
    {
        $attributes = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')
            ->getItems();

        $mattributecode = '--please select--';
        $magentoattributeCodeArray[''] = $mattributecode;
        $magentoattributeCodeArray['default'] = "--Set Default Value--";
        foreach ($attributes as $attribute){
            $magentoattributeCodeArray[$attribute->getAttributecode()] = $attribute->getFrontendLabel();
        }
        return $magentoattributeCodeArray;
    }

    /**
     * @return array|mixed
     */
    public function getMappedAttribute()
    {
        $data = $this->_trademeAttribute[0]['value'];
        if($this->_profile && $this->_profile->getId()>0){
            $data = json_decode($this->_profile->getOptReqAttribute(), true);
            if(isset($data['required_attributes']) && isset($data['optional_attributes']))
                $data = array_merge($data['required_attributes'], $data['optional_attributes']);
        }
        return $data;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
}
