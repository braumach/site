<?php

namespace Zemez\Amp\Block\Page\Head\Json;

use Magento\Store\Model\ScopeInterface;

class Product extends \Magento\Catalog\Block\Product\AbstractProduct
{
    const NULL_PRODUCT_NAME = 'Null_Product_Name';
    const NULL_PRODUCT_SHORT_DESCRIPTION = 'Null_Product_short_description';
    const NULL_PRODUCT_STATUS = 'OutOfStock';
    const DEFAULT_CURRENCY = 'USD';
    const PRODUCT_IMAGE_WIDTH = 480;
    const PRODUCT_IMAGE_HEIGHT = 480;


    /**
     * @var Zemez\Amp\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Framework\Registry $coreRegistry,
     * @param \Zemez\Amp\Helper\Data $helper,
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Zemez\Amp\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_helper = $helper;
    }

    /**
     * Retrieve string by JSON format according to http://schema.org requirements
     * @return string
     */

    public function getJson()
    {
        /**
         * Get helper, product and store objects
         */
        $_product = $this->getProduct();
        $_store = $_product->getStore();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $reviewFactory = $objectManager->create('Magento\Review\Model\Review');
        $reviewFactory->getEntitySummary($_product, $_store->getId());

        /**
         * Set product data from product object
         */
        if ($_product) {
            /**
             * Get product name
             */
            if (strlen($_product->getName())) {
                $productName = $this->escapeHtml($_product->getName());
            }

            /**
             * Get product image
             */
            $productImage = $this->getImage($_product, 'product_page_image_small', [])->getData('image_url');

            /**
             * Get product description
             */
            $productShortDescription = '';
            if (strlen($_product->getShortDescription())) {
                $productShortDescription = $this->escapeHtml($_product->getShortDescription());
            } elseif ($_product->getDescription()) {
                $productShortDescription = $this->escapeHtml($_product->getDescription());
            }
        }

        $siteName = $this->_scopeConfig->getValue('general/store_information/name', ScopeInterface::SCOPE_STORE);
        if (!$siteName) {
            $siteName = 'Magento Store';
        }

        $logoBlock = $this->getLayout()->getBlock('logo');
        $logo = $logoBlock ? $logoBlock->getLogoSrc() : '';

        if ($this->pageConfig->getTitle()->get()) {
            $pageContentHeading = $this->pageConfig->getTitle()->get();
        } else {
            $pageContentHeading = $productName;
        }
        if($_product->getIsSalable()) {
            $availability = 'http://schema.org/InStock';
        } else {
            $availability = 'http://schema.org/OutOfStock';
        }

        $json = array(
            "@context" => "http://schema.org",
            "@type" => "Product",
            "name" => $productName,
            "description" => $productShortDescription,
            "sku" => $_product->getSku(),
            //"author" => $siteName,
            "image" => array(
                '@type' => 'ImageObject',
                'url' => $productImage,
                'width' => self::PRODUCT_IMAGE_WIDTH,
                'height' => self::PRODUCT_IMAGE_HEIGHT,
            ),
            "offers" => array(
                '@type' => 'Offer',
                'availability' => $availability,
                'price' => $_product->getFinalPrice(),
                'priceCurrency' => $_store->getCurrentCurrencyCode(),
                'url' => $_product->getProductUrl()
            )
            //"datePublished" => $_product->getCreatedAt(),
            //"dateModified" => $_product->getUpdatedAt(),
            //"headline" => mb_substr($pageContentHeading, 0, 110, 'UTF-8'),
//            "publisher" => array(
//                '@type' => 'Organization',
//                'name' => $siteName,
//                'logo' => array(
//                    '@type' => 'ImageObject',
//                    'url' => $logo,
//                ),
//            ),
//            "mainEntityOfPage" => array(
//                "@type" => "WebPage",
//                "@id" => $this->getUrl(),
//            ),
        );
        if ($_product->getRatingSummary()->getReviewsCount() > 0) {
            $aggregateRating = [
                "aggregateRating" => array(
                    '@type' => 'AggregateRating',
                    'ratingValue' => $_product->getRatingSummary()->getRatingSummary(),
                    'reviewCount' => $_product->getRatingSummary()->getReviewsCount(),
                    'bestRating' => 100,
                    'worstRating' => 1
                ),
            ];
            return str_replace('\/', '/', json_encode(array_merge($json, $aggregateRating)));
        }

        return str_replace('\/', '/', json_encode($json));
    }
}