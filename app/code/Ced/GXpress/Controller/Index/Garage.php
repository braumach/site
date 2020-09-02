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

namespace Ced\GXpress\Controller\Index;

use \Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Garage extends Action
{
    

    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        Context $context,
        \Magento\Catalog\Model\ProductFactory $product
    )
    {
        $this->_categoryFactory = $categoryFactory;
        $this->productFactory = $product->create();
        parent::__construct($context);
    }

    public function execute()
    {

        $categoryId = $this->_request->getParam('make');

        if($categoryId){
        
            $product = $this->getCategory($categoryId)
                            ->getProductCollection()
                            ->addAttributeToSelect('model')
                            ->addAttributeToFilter('model',['neq' => ""]);
                            $ar = $product->getData();
            $uniqueModel = array_unique(array_map(function ($i) { return $i['model']; }, $ar)); 

            die(json_encode($uniqueModel));
        }

        $model = $this->_request->getParam('model');
        $categoryId = $this->_request->getParam('category');
        if($model && $categoryId)
        {
            $product = $this->getCategory($categoryId)
                        ->getProductCollection()
                        ->addAttributeToSelect('year')
                        ->addAttributeToFilter('year',['neq' => ""]);

            $uniqueYear = array_unique(array_map(function ($i) { return $i['year']; }, $product->getData())); 

            die(json_encode($uniqueYear));
        }

        $submit_make = $this->_request->getParam('submit_make');
        if($submit_make)
        {
            $params = $this->_request->getParams();

            /* @todo

                /parts page search for make , model , year , series and show brand, category , filter based result . designer needed.   

            */
             //$product = $this->getCategory($categoryId);
            die(json_encode($submit_make));
        }                     
    
    }

    /**
 * Get category object
 *
 * @return \Magento\Catalog\Model\Category
 */
public function getCategory($categoryId)
{
    $this->_category = $this->_categoryFactory->create();
    $this->_category->load($categoryId);
    return $this->_category;
}

/**
 * Get all children categories IDs
 *
 * @param boolean $asArray return result as array instead of comma-separated list of IDs
 * @return array|string
 */
public function getAllChildren($asArray = false, $categoryId = false)
{
    if ($this->_category) {
        return $this->_category->getAllChildren($asArray);
    } else {
        return $this->getCategory($categoryId)->getAllChildren($asArray);
    }
}

public function getProductCollection($category_id_array)
{
    $collection = $this->_productCollectionFactory->create();
    $collection->addAttributeToSelect('*');
    $collection->addCategoriesFilter(['in' => $category_id_array]);
    $collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
    $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
    $collection->setPageSize(9); // fetching only 9 products
    return $collection;
}
}