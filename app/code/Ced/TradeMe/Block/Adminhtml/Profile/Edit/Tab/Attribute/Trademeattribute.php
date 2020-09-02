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

use Ced\TradeMe\Helper\Data;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use \Magento\Backend\Block\Widget;

/**
 * Class Trademeattribute
 * @package Ced\TradeMe\Block\Adminhtml\Profile\Edit\Tab\Attribute
 */
class Trademeattribute extends Widget implements RendererInterface

{
    /**
     * @var string
     */
    public $_template = 'Ced_TradeMe::profile/attribute/trademeattribute.phtml';
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public  $_objectManager;
    /**
     * @var \Magento\Framework\Registry
     */
    public  $_coreRegistry;
    /**
     * @var mixed
     */
    public  $_profile;
    /**
     * @var
     */
    public  $_trademeAttribute;
    /**
     * @var
     */
    public $_trademeAttributeFeature;
    /**
     * @var Data
     */
    public $helper;

    public $logger;

    public $categoryFactory;

    /**
     * Trademeattribute constructor.
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
        \Ced\TradeMe\Helper\Logger $logger,
        \Ced\TradeMe\Model\CategoryFactory $categoryFactory,
        Data $helper,
        array $data = []
    )
    {
        $this->_objectManager = $objectManager;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->categoryFactory = $categoryFactory;
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
        )->setData(['label' => 'Add Attribute', 'onclick' =>'return trademeAttributeControl.addItem()', 'class' => 'add']);

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
        try {
            $catId = $this->getCatId();
            $requiredAttributes = $optionalAttribues = [];
            if ($this->_profile && $this->_profile->getId() > 0) {
                $catId = ($this->_profile->getProfileCategory());

            }
            if ($catId) {
                $getAttribute = $this->helper->fetchCatAttr($catId);
                $category = $this->categoryFactory->create()->loadByField('trademe_id', $catId);
                $category->setAttributes(json_encode($getAttribute));
                $category->save();
                $response = isset($getAttribute['Attributes']) ? $getAttribute['Attributes'] : $getAttribute;
                if (isset($getAttribute['Attributes'])) {
                foreach ($response as $value) {
                    $options = array();
                    if (isset($value['Options'])) {
                        $temOptions = array();
                        foreach ($value['Options'] as $opt) {
                            $temOptions = array('_value' => array('name' => $opt['Display']),
                                '_attribute' => array('id' => $opt['Value']));
                            $options[] = $temOptions;
                        }
                    }
                    $val = [];
                    foreach ($options as $option) {
                        $val[] = $option['_value']['name'];
                    }
                    $allowValueIds = !empty($options) ? array('value' => $options) : '';
                    if ($allowValueIds == '' && isset($value['Type']) && $value['Type'] == 1) {
                        $allowedValues['value'] = array(
                            array('_value' =>
                                array('name' => 'true'), '_attribute' => array('id' => 1)), array('_value' => array('name' => 'false'), '_attribute' => array('id' => 0)));
                    }

                    if (isset($value['IsRequiredForSell'])) {

                        $requiredAttributes [$value['Name']] = array(
                            'trademe_attribute_name' => $value['DisplayName'],
                            'trademe_attribute_type' => 'LABEL',
                            'magento_attribute_code' => '',
                            'required' => 1,
                            'trademe_attribute_enum' => implode(',', $val)
                        );
                    } else {
                        $optionalAttributes [$value['Name']] = array(
                            'trademe_attribute_name' => $value['DisplayName'],
                            'trademe_attribute_type' => 'LABEL',
                            'magento_attribute_code' => '',
                            'required' => 0,
                            'trademe_attribute_enum' => $allowValueIds
                        );
                    }
                }
            }

            }

            $this->_trademeAttribute[] = [
                'label' => __('Required Attributes'),
                'value' => $requiredAttributes
            ];

            $this->_trademeAttribute[] = [
                'label' => __('Optional Attributes'),
                'value' => $optionalAttribues
            ];
            return $this->_trademeAttribute;
        } catch (\Exception $e) {
            $this->logger->addError('In Trademe Attributes: has exception '.$e->getMessage(), ['path' => __METHOD__]);
            return false;
        }
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
        $data = $this->_trademeAttribute[0]['value'];
        $temp = $temp2 = [];
        if($this->_profile && $this->_profile->getId()>0){
            $data = json_decode($this->_profile->getCatDependAttribute(), true);
            if(isset($data['required_attributes']) && isset($data['optional_attributes'])) {
                $data = array_merge($data['required_attributes'], $data['optional_attributes']);

            } else {
                $temp = $data['required_attributes'];
            }

        }
        if ($temp && count($temp) > 0)
            $data = $temp;
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
