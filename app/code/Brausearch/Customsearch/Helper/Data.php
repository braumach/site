<?php
/**
 *
 */

namespace Brausearch\Customsearch\Helper;

use \Magento\Eav\Model\Config;
use \Magento\Theme\Block\Html\Header\Logo;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Catalog\Model\Product\AttributeSet\Options;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{   

    protected $_eavConfig;
    protected $_logo;
    protected $_storeManager;
    protected $_attributeSet;

    public function __construct(
        Config $eavConfig,
        Logo $logo,
        StoreManagerInterface $storeManager,
        Options $attributeSet
    ){
        $this->_eavConfig = $eavConfig;
        $this->_logo = $logo;
        $this->_storeManager = $storeManager;
        $this->_attributeSet = $attributeSet;
    }


    public function getAttribute($attribute)
    {   
        $attribute = $this->_eavConfig->getAttribute('catalog_product',$attribute);
        $options = $attribute->getSource()->getAllOptions();

        $values = [];

        foreach($options as $cal=>$val){
            $values[$val['value']] = $val['label']->getText();
        }

        return $values;
    }

    public function getAttributeSets() {
        $attrSets = $this->_attributeSet->toOptionArray();

        return $attrSets;
    }

    public function isHomePage()
    {
        return $this->_logo->isHomePage();
    }

    public function getStoreCode() {
        return $this->_storeManager->getStore()->getCode();
    }
}