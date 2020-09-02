<?php

namespace Brausearch\Customsearch\Controller\Brau;

use \Magento\Framework\App\Action\Context;
use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use \Magento\Framework\Registry;
use \Magento\Store\Model\StoreManagerInterface;

class Search extends \Magento\Framework\App\Action\Action
{	

	CONST REDIRECT_PRODUCT_PAGE = 1;
	CONST BRAUMACH_STORE_ID = 1;
	CONST WGIT_STORE_ID = 2;

	protected $_productCollectionFactory;
    protected $_registry;
    protected $_storeManager;

	public function __construct(
		Context $context, 
        Registry $registry,
		CollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager
	){ 
        $this->_registry = $registry;
		$this->_productCollectionFactory = $productCollectionFactory;
        $this->_storeManager = $storeManager;
		parent::__construct($context);
	}

	public function execute()
    {
        $product_type = $this->_request->getParam('brau-type') ? $this->_request->getParam('brau-type') : 0;
        $brand_name = $this->_request->getParam('brau-brand-text') ? $this->_request->getParam('brau-brand-text') : 0;
    	$brand_id = $this->_request->getParam('brau-brand');
        $model = $this->_request->getParam('brau-model');
        $year = $this->_request->getParam('brau-year');
        $storeId = $this->_storeManager->getStore()->getStoreId();

        if ((self::WGIT_STORE_ID == $storeId && $product_type == 0) || (self::BRAUMACH_STORE_ID==$storeId && $brand_id == 0)) {
            $resultRedirect = $this->_redirect('/');
            return $resultRedirect;
        }

        // Collect attribute data
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect([
            'attribute_set_id',
            'entity_id',
            'brand_id',
            'model',
            'year',
            'url_key',
            'image',
            'name',
            'price',
            'sku',
            'driver_length',
            'passenger_length',
            'rear_length',
            'length',
            'width',
            'height'
        ]);

        if (self::WGIT_STORE_ID == $storeId && 0!=$product_type)
            $collection->addAttributeToFilter('attribute_set_id',array('eq' => $product_type));
        if(0!=$brand_id)
            $collection->addAttributeToFilter('brand_id',array('eq' => $brand_id));
        if(0!=$model)
            $collection->addAttributeToFilter('model',array('eq' => $model));
        if(0!=$year)
            $collection->addAttributeToFilter('year',array('eq' => $year));

        $collection->addAttributeToFilter('visibility', array(2,3,4));
        $collection->addAttributeToFilter('status',1);
        $collection->setOrder('price','DESC');
        $collection->addStoreFilter($storeId);

        // Add filter for in stock and qty > 0

        $data = [];
        $url_key = [];

        if (count($collection) > 0) {

            foreach($collection as $product){
                $getData = array_merge($product->getData(), array('brand_name'=>$brand_name));
                $data[] = $getData;
                $url_key[] = $product->getUrlKey();
            }

            if (count($data)==self::REDIRECT_PRODUCT_PAGE) {
                $redirect = $this->_redirect($url_key[0] . '.html');
                return $redirect;

            } else{
                $jsonResult = json_encode($data);

                // Create registry to be fetched by block
                $this->_registry->register('brau_registry',$jsonResult);

                $this->_view->loadLayout();
                $this->_view->getLayout()->initMessages();
                $this->_view->renderLayout();
            }
        }
        else {
            $resultRedirect = $this->_redirect('no-route');
            return $resultRedirect;
        }
    }
}