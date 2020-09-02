<?php

namespace Brausearch\Customsearch\Controller\Brau;

use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use \Magento\Store\Model\StoreManagerInterface;

class Ajax extends \Magento\Framework\App\Action\Action
{

    CONST WGIT_STORE_ID = 2;

	protected $_resultJsonFactory;
	protected $_productCollectionFactory;
	protected $_storeManager;

	public function __construct(
		Context $context, 
		JsonFactory $resultJsonFactory,
		CollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager
	){
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_productCollectionFactory = $productCollectionFactory;
		$this->_storeManager = $storeManager;
		parent::__construct($context);
	}

    public function execute()
    {
        $param = $this->_request->getParam('param');
        $attribute = $this->_request->getParam('attribute');
        $value = $this->_request->getParam('value');
        $attribute_set_key = $this->_request->getParam('attribute_set_key');
        $storeId = $this->_storeManager->getStore()->getStoreId();

        if('ajax=1' == $param)
        {
            if('brau-type' == $attribute)
                $attribute_code = array('attribute_set_id','brand_id');
            elseif('brau-brand' == $attribute)
                $attribute_code = array('brand_id','model');
            elseif('brau-model' == $attribute)
                $attribute_code = array('model','year');

            $collection = $this->_productCollectionFactory->create();
            $collection->addAttributeToSelect([$attribute_code[0],$attribute_code[1]]);

            // WGIT FILTER - INCLUDE ATTRIBUTE_SET_ID DURING BRAND AND MODEL SELECTION
            if(self::WGIT_STORE_ID == $storeId && ('brau-brand'==$attribute || 'brau-model'==$attribute))
                $collection->addAttributeToFilter('attribute_set_id',array('eq' => $attribute_set_key,'notnull' => true));

            $collection->addAttributeToFilter($attribute_code[0],array('eq' => $value,'notnull' => true));
            $collection->addAttributeToFilter($attribute_code[1],array('notnull' => true));
            $collection->addAttributeToFilter('visibility',array(2,3,4));
            $collection->addAttributeToFilter('status',1);
            $collection->setOrder($attribute_code[1],'ASC');
            $collection->addStoreFilter($storeId);

            $arr = [];
            foreach($collection as $data){
                $arr[] = $data->getData($attribute_code[1]);
            }

            $result = $this->_resultJsonFactory->create();
            $result = $result->setData(array_unique($arr));

            return $result;
        }

        else 
        {
            $resultRedirect = $this->_redirect('no-route');
            return $resultRedirect;
        }
    }
}