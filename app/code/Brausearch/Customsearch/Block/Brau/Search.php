<?php

namespace Brausearch\Customsearch\Block\Brau;

// use \Magento\Framework\Registry;
use \Magento\Catalog\Block\Product\Context;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Catalog\Model\ProductFactory;
use \Magento\Catalog\Block\Product\ListProduct;
use \Magento\Framework\Data\Form\FormKey;


class Search extends \Magento\Framework\View\Element\Template {

    protected $_registry;
    protected $_objectManager;
    protected $_productloader;
    protected $_listProduct;
    protected $_formkey;

    public function __construct(
    	Context $context, 
        ProductFactory $productloader,
        ListProduct $listProduct,
        FormKey $formkey,
        ObjectManagerInterface $objectManager,
    	array $data = []
    ) 
    {
        $this->_formkey = $formkey;
        $this->_listProduct = $listProduct;
        $this->_productloader = $productloader;
        $this->_objectManager = $objectManager;
        $this->_registry = $context->getRegistry();
        parent::__construct($context,$data);

    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getParam() {

    	return $this->getRequest()->getParams();
    }

    public function getRegistry() {

        $registry = $this->_registry->registry('brau_registry');

        $registry = json_decode($registry,true);
        
        return $registry;
    }

    public function getMediaUrl() {

        $media_dir = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                
        $media_dir = $media_dir . "catalog/product";

        return $media_dir;
    }

    public function getBaseUrl() {

        $baseurl = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
                    ->getStore()
                    ->getBaseUrl();

        return $baseurl;

    }

    public function addToCartUrl($id) {

        $product = $this->_productloader->create()->load($id);
        $addToCartUrl = $this->_listProduct->getAddToCartUrl($product);

        return $addToCartUrl;
    }

    public function getFormKey() {

        $formKey = $this->_formkey->getFormKey();

        return $formKey;
    }

    public function getCurrentStoreId() {
        return $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
    }
}