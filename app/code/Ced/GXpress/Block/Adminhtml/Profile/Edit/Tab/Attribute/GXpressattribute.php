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
 * @package   Ced_GXpress
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GXpress\Block\Adminhtml\Profile\Edit\Tab\Attribute;

use Ced\GXpress\Helper\Data;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use \Magento\Backend\Block\Widget;

/**
 * Class GXpressattribute
 * @package Ced\GXpress\Block\Adminhtml\Profile\Edit\Tab\Attribute
 */
class GXpressattribute extends Widget implements RendererInterface

{
    /**
     * @var string
     */
    public $_template = 'Ced_GXpress::profile/attribute/gxpressattribute.phtml';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $_objectManager;
    /**
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry;
    /**
     * @var mixed
     */
    public $_profile;
    /**
     * @var
     */
    public $_gxpressAttribute;
    /**
     * @var
     */
    public $_gxpressAttributeFeature;
    /**
     * @var Data
     */
    public $helper;

    /**
     * GXpressattribute constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        Data $helper,
        array $data = []
    )
    {
        $this->_objectManager = $objectManager;
        $this->helper = $helper;
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
        )->setData(['label' => 'Add Attribute', 'onclick' => 'return gxpressAttributeControl.addItem()', 'class' => 'add']);

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
    public function getGXpressAttributes()
    {
        $catId = $this->getCatId();
        if($this->_profile && $this->_profile->getId()>0) {
            $catArray = json_decode($this->_profile->getProfileCategory(), true);
            if ($catArray) {
                foreach ($catArray as $value) {
                    if ($value != "") {
                        $catId = $value;
                        break;
                    }
                }
            }
        }

        $category = $this->_objectManager->get('Ced\GXpress\Model\Category')->load($catId, 'csv_firstlevel_id');
        $required = $category->getData('gxpress_required_attributes');
        $optional = $category->getData('gxpress_attributes');
        $required = explode(",", $required);
        $optional = explode(",", $optional);
        $requiredAttributes = $optionalAttribues = [];
        $attrCollection = $this->_objectManager->create('Ced\GXpress\Model\Attribute')->getCollection();
        $reqattr = $attrCollection->addFieldToFilter('gxpress_attribute_name', $required);
        $reqattrs = $reqattr->getData();
        foreach ($reqattrs as $req) {
            $requiredAttributes [$req['gxpress_attribute_name']] = [
                'gxpress_attribute_name' => $req['gxpress_attribute_name'], 'gxpress_attribute_type' => $req['gxpress_attribute_type'],
                'gxpress_attribute_enum' => $req['gxpress_attribute_enum'], 'magento_attribute_code' => $req['magento_attribute_code'], 'required' => 1
            ];
        }
        $attrCollection = $this->_objectManager->create('Ced\GXpress\Model\Attribute')->getCollection();
        $optattr = $attrCollection->addFieldToFilter('gxpress_attribute_name', $optional);
        $optattrs = $optattr->getData();
        foreach ($optattrs as $opt) {
            $optionalAttribues [$opt['gxpress_attribute_name']] = [
                'gxpress_attribute_name' => $opt['gxpress_attribute_name'], 'gxpress_attribute_type' => $opt['gxpress_attribute_type'],
                'gxpress_attribute_enum' => $opt['gxpress_attribute_enum'], 'magento_attribute_code' => $opt['magento_attribute_code'], 'required' => ''
            ];
        }
        $this->_gxpressAttribute[] = [
            'label' => __('Required Attributes'),
            'value' => $requiredAttributes
        ];

        $this->_gxpressAttribute[] = [
            'label' => __('Optional Attributes'),
            'value' => $optionalAttribues
        ];
        return $this->_gxpressAttribute;
    }


    /**
     * @return mixed
     */
    public function getMagentoAttributes()
    {
        $attributes = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')->getItems();
        $mattributecode = '--please select--';
        $magentoattributeCodeArray[''] = $mattributecode;
        $magentoattributeCodeArray['default'] = "--Set Default Value--";
        foreach ($attributes as $attribute) {
            $magentoattributeCodeArray[$attribute->getAttributecode()] = $attribute->getFrontendLabel();
        }
        return $magentoattributeCodeArray;
    }

    /**
     * @return array|mixed
     */
    public function getMappedAttribute()
    {
        $data = $this->_gxpressAttribute[0]['value'];
        if ($this->_profile && $this->_profile->getId() > 0) {
            $data = json_decode($this->_profile->getProfileCatAttribute(), true);
            if (isset($data['required_attributes']) && isset($data['optional_attributes']))
                $data = array_merge($data['required_attributes'], $data['optional_attributes']);
        }
        return isset($data) ? $data : array();
    }

    /**
     * @return string
     */
    public function getSavedCatFeatures()
    {
        $data = "";
        if ($this->_profile && $this->_profile->getId() > 0)
            $data = $this->_profile->getProfileCatFeature();
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
