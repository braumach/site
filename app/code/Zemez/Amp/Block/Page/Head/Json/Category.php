<?php

namespace Zemez\Amp\Block\Page\Head\Json;

use Magento\Store\Model\ScopeInterface;

class Category extends \Magento\Framework\View\Element\Template
{
    const NULL_CATEGORY_NAME = 'Category Name';
    const NULL_CATEGORY_DESCRIPTION = 'Category Description';
    const LOGO_IMAGE_WIDTH = 270;
    const LOGO_IMAGE_HEIGHT = 60;
    const THUMB_WIDTH = 100;
    const THUMB_HEIGHT = 100;

    /**
     * @var Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var Zemez\Amp\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Category constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Zemez\Amp\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Zemez\Amp\Helper\Data $helper,
        array $data = []
    ) {
        $this->_categoryFactory = $categoryFactory;
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
        $this->_helper = $helper;

    }

    /**
     * Retrieve string by JSON format according to http://schema.org requirements
     * @return string
     */

    public function getCategory($categoryId)
    {
        $category = $this->_categoryFactory->create();
        $category->load($categoryId);
        return $category;
    }

    /**
     * @param $categoryId
     * @return mixed
     */

    public function getCategoryProducts($categoryId)
    {
        $products = $this->getCategory($categoryId)->getProductCollection();
        $products->addAttributeToSelect('*');
        return $products;
    }

    /**
     * @return mixed
     */
    public function getJson()
    {

        //$siteName = $this->_scopeConfig->getValue('general/store_information/name', ScopeInterface::SCOPE_STORE);
        //$logoBlock = $this->getLayout()->getBlock(' logo');
        //$logo = $logoBlock ? $logoBlock->getLogoSrc() : '';

        // $categoryName = $currentCategory->getName() ? $currentCategory->getName() : self::NULL_CATEGORY_NAME;
//        $categoryDescription = $this->pageConfig->getDescription() ? mb_substr($this->pageConfig->getDescription(), 0, 250, 'UTF-8') : self::NULL_CATEGORY_DESCRIPTION;
//        $categoryCreatedAt = $currentCategory->getCreatedAt() ? $currentCategory->getCreatedAt() : '';
//        $categoryUpdatedAt = $currentCategory->getUpdatedAt() ? $currentCategory->getUpdatedAt() : '';
        // $this->pageConfig->getTitle()->get()

        $currentCategory = $this->_coreRegistry->registry('current_category');
        $categoryId = $currentCategory->getId();
        $categoryProducts = $this->getCategoryProducts($categoryId);

        $objectManager =\Magento\Framework\App\ObjectManager::getInstance();
        $helperImport = $objectManager->get('\Magento\Catalog\Helper\Image');

        $itemListElement  = [];

        foreach ($categoryProducts as $product) {

            $imageUrl = $helperImport->init($product, 'category_page_grid')->setImageFile($product->getSmallImage())->getUrl();

            $price = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount();

            $result  = [
                '@type' => 'Product',
                'image' => $imageUrl,
                'url' => $product->getProductUrl(),
                'name' => $product->getName(),
                //"position" => $product->getEntityId(),
                'offers' => array(
                    '@type' => 'Offer',
                    'price' => $price,
                ),];
            $itemListElement["itemListElement"][] = $result;
        }

        // Set scheme JSON data
        $json = array(
            "@context" => "http://schema.org",
            "@type" => "ItemList",
            "url" => $currentCategory->getUrl(),
            "numberOfItems" => $currentCategory->getProductCollection()->Count(),
        );
        return str_replace('\/', '/', json_encode(array_merge($json, $itemListElement)));
    }
}