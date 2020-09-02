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

namespace Ced\GXpress\Helper;

class GXpress extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    public $_curl;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;
    /**
     * @var mixed
     */
    public $scopeConfigManager;
    /**
     * @var mixed
     */
    public $adminSession;
    /**
     * @var \Magento\Framework\Message\Manager
     */
    public $messageManager;
    /**
     * DirectoryList
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    public $directoryList;
    /**
     * Json Parser
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $json;
    /**
     * @var \Magento\Framework\HTTP\Adapter\Curl
     */
    public $_resource;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Ced\GXpress\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var
     */
    public $_siteID;

    public $token;

    /**
     * GXpress constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Message\Manager $manager
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Json\Helper\Data $json
     * @param \Magento\Directory\Model\CountryFactory $country
     * @param \Magento\Framework\HTTP\Adapter\Curl $curl
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\Manager $manager,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Directory\Model\CountryFactory $country,
        \Ced\GXpress\Helper\Logger $logger,
        \Magento\Framework\HTTP\Adapter\Curl $curl,
        \Magento\Framework\Registry $registry,
        \Ced\GXpress\Helper\MultiAccount $multiAccountHelper
    )
    {
        $this->objectManager = $objectManager;
        $this->_resource = $curl;
        parent::__construct($context);
        $this->multiAccountHelper = $multiAccountHelper;
        $this->_coreRegistry = $registry;
        $account = false;
        if ($this->_coreRegistry->registry('gxpress_account')) {
            $account = $this->_coreRegistry->registry('gxpress_account');
        }
        $this->messageManager = $manager;
        $this->directoryList = $directoryList;
        $this->json = $json;
        $this->_country = $country;
        $this->logger = $logger;
        $this->adminSession = $this->objectManager->create('Magento\Backend\Model\Session');
        $this->configResourceModel = $this->objectManager->create('\Magento\Config\Model\ResourceModel\Config');
        $this->token = ($account) ? trim($account->getAccountToken()) : $this->scopeConfig->getValue('gxpress_config/gxpress_setting/token');
        $this->_siteID = ($account) ? trim($account->getAccountLocation()) : $this->scopeConfig->getValue('gxpress_config/gxpress_setting/location');
    }

    public function updateAccountVariable() {
        $account = false;
        if ($this->_coreRegistry->registry('gxpress_account')) {
            $account = $this->_coreRegistry->registry('gxpress_account');
        }
        $this->environment = ($account) ? trim($account->getAccountEnv()) : $this->scopeConfig->getValue('gxpress_config/gxpress_setting/environment');
        $this->token = ($account) ? trim($account->getAccountToken()) : $this->scopeConfig->getValue('gxpress_config/gxpress_setting/token');
    }

    /**
     * @param $product
     * @param $type
     * @return array
     */
    public function prepareData($product, $type=null)
    {
        try {
            $account = $this->_coreRegistry->registry('gxpress_account');
            $profileIdAccAttr = $this->multiAccountHelper->getProfileAttrForAcc($account->getId());
            //image select
            if (is_string($product)) {
                $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($product);
            }
            $content = array();
            $error = array();
            $allImg = $product->getMediaGallery('images');

            foreach ($allImg as $value) {
                $pictureUrls[] = $this->objectManager->get('Magento\Catalog\Model\Product\Media\Config')->getMediaUrl($value['file']);
            }
            if (empty($pictureUrls)) {
                $error['image'] = 'Product SKU:'.$product->getSku().' product images not found';
                //return $content;
            }

            $profileId = $product->getData($profileIdAccAttr);

            if(!$profileId) {
                $confproductId = $this->objectManager
                    ->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')
                    ->getParentIdsByChild($product->getId());

                $confproduct = $this->objectManager
                    ->create('Magento\Catalog\Model\Product')
                    ->load($confproductId);

                $Variants = $this->createConfigProduct($confproduct);

                $profileId = $confproduct->getData($profileIdAccAttr);
            }

            $profileData = $this->objectManager->get('Ced\GXpress\Model\Profile')->load($profileId);
            $catArray = $profileData->getProfileCategory();
            $catArray = json_decode($catArray,true);
            $configAttrlist = $profileData->getprofile_cat_attribute();
            $catSpecifics = json_decode($configAttrlist, true);


            if (!empty($catSpecifics['required_attributes'])) {
                foreach ($catSpecifics['required_attributes'] as $reqAttr) {
                    $catValue = $reqAttr['magento_attribute_code'] =='default' ? $reqAttr['default'] : $product->getData($reqAttr['magento_attribute_code']);

                    if(empty($catValue) && isset($confproduct) && ($reqAttr['gxpress_attribute_name'] == 'brand')) {
                        $catValue = $reqAttr['magento_attribute_code'] == 'default' ? $reqAttr['default'] : $confproduct->getData($reqAttr['magento_attribute_code']);
                    }

                    if (empty($catValue)) {
                        $error[$reqAttr['magento_attribute_code']] =
                            'Product SKU:'.$product->getSku()." please fill " . $reqAttr['magento_attribute_code'] . " attribute's value";
                        continue;
                    } else {
                        if($reqAttr['gxpress_attribute_name'] == 'productId') {
                            $barcode = $this->objectManager->create('Ced\GXpress\Helper\Barcode');
                            $barcode->setBarcode($catValue);
                            if(!($barcode->valid)) {
                                $error[$reqAttr['magento_attribute_code']] =
                                    'Product SKU:'.$product->getSku()." please fill correct value for: " . $reqAttr['magento_attribute_code'] . " attribute";
                            }
                        }
                        $content[$reqAttr['gxpress_attribute_name']] = $catValue;
                    }
                }
            }

            if (!empty($catSpecifics['optional_attributes'])) {
                foreach ($catSpecifics['optional_attributes'] as $optAttr) {
                    $catValue = $optAttr['magento_attribute_code'] =='default' ? $optAttr['default'] : $product->getData($optAttr['magento_attribute_code']);
                    if (empty($catValue)) {
                        $error[$optAttr['magento_attribute_code']] = 'Product SKU:'.$product->getSku()." please fill " . $optAttr['magento_attribute_code'] . " attribute's value";
                        continue;
                    } else {
                        $content[$optAttr['magento_attribute_code']] = $catValue;
                    }
                }
            }
            $content['error'] = array();
            $content['error'] = array_merge($content['error'],$error);
        } catch (\Exception $e) {
            $this->logger->addError('In Prepare Data: Product SKU:'.$product->getSku().' has exception '.$e->getMessage(), ['path' => __METHOD__]);
            $content = 'Product SKU:'.$product->getSku().' has exception '.$e->getMessage();
        }
        return $content;
    }
    /**
     * @param $product
     * @param $reqOptAttr
     * @return array
     */
    public function reqOptAttributeData($product, $reqOptAttr)
    {
        try {
            $item = [];
            if ($product->getGXpressItemId() != '') {
                $item['ItemID'] = $product->getGXpressItemId();
            }
            $error = false;
            $msg = "";
            foreach ($reqOptAttr['required_attributes'] as $reqAttr) {
                switch ($reqAttr['gxpress_attribute_name']) {
                    case 'name':
                        $item['Title'] = $reqAttr['magento_attribute_code'] =='default' ? $reqAttr['default'] : html_entity_decode(substr($product->getData($reqAttr['magento_attribute_code']), 0, 80));
                        
                        if (empty($item['Title'])) {
                            $error = true;
                            $msg = "Title is missing";
                        }
                        break;
                    case 'sku':
                        $item['SKU'] = $reqAttr['magento_attribute_code'] =='default' ? $reqAttr['default'] : $product->getData($reqAttr['magento_attribute_code']);
                        if (empty($item['SKU'])) {
                            $error = true;
                            $msg = "SKU is missing";
                        }
                        break;
                    
                    case 'description':
                        $item['Description'] = $reqAttr['magento_attribute_code'] =='default' ? $this->getDescriptionTemplate($product, $reqAttr['default']) : '<![CDATA['.$product->getData($reqAttr['magento_attribute_code']).']]>';
                        if (empty($item['Description'])) {
                            $error = true;
                            $msg = "Description is missing";
                        }
                        break;
                    case 'inventory':
                        $quantity = $reqAttr['magento_attribute_code'] =='default' ? $reqAttr['default'] : $product->getData($reqAttr['magento_attribute_code']);
                        $item['Quantity'] = isset($quantity['qty']) ? $quantity['qty'] : $quantity;
                        if ($item['Quantity'] < 0) {
                            $stockItem = $this->objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
                            $stock = $stockItem->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
                            $qty = $stock->getQty();
                            $item['Quantity'] = $qty;
                        }
                        if ($item['Quantity'] < 0) {
                            $error = true;
                            $msg = "Quantity is missing";
                        }
                        
                    case 'max_dispatch_time':
                        $item['DispatchTimeMax'] = $reqAttr['magento_attribute_code'] =='default' ? $reqAttr['default'] : $product->getData($reqAttr['magento_attribute_code']);
                        if (empty($item['DispatchTimeMax'])) {
                            $error = true;
                            $msg = "Dispatch Time Max is missing";
                        }
                        break;
                    case 'listing_type':
                        $item['ListingType'] = $reqAttr['magento_attribute_code'] =='default' ? $reqAttr['default'] : $product->getData($reqAttr['magento_attribute_code']);
                        if (empty($item['ListingType'])) {
                            $error = true;
                            $msg = "ListingType is missing";
                        }
                        break;
                    case 'listing_duration':
                        $item['ListingDuration'] = $reqAttr['magento_attribute_code'] =='default' ? $reqAttr['default'] : $product->getData($reqAttr['magento_attribute_code']);
                        if (empty($item['ListingDuration'])) {
                            $error = true;
                            $msg = "Listing Duration is missing";
                        }
                        break;
                    default:
                        break;
                }
                if ($error) {
                    break;
                }
            }
            if (!empty($reqOptAttr['optional_attributes'])) {
                foreach ($reqOptAttr['optional_attributes'] as $optAttr) {
                    switch ($optAttr['gxpress_attribute_name']) {
                    case 'upc':
                        $item['ProductListingDetails']['UPC'] = $optAttr['magento_attribute_code'] =='default' ? $optAttr['default'] : $product->getData($optAttr['magento_attribute_code']);
                        break;
                    case 'ean':
                        $item['ProductListingDetails']['EAN'] = $optAttr['magento_attribute_code'] =='default' ? $optAttr['default'] : $product->getData($optAttr['magento_attribute_code']);
                        break;
                    case 'isbn':
                        $item['ProductListingDetails']['ISBN'] = $optAttr['magento_attribute_code'] =='default' ? $optAttr['default'] : $product->getData($optAttr['magento_attribute_code']);
                        break;
                    case 'brand':
                        $item['ProductListingDetails']['BrandMPN']['Brand'] = $optAttr['magento_attribute_code'] =='default' ? $optAttr['default'] : $product->getData($optAttr['magento_attribute_code']);
                        break;
                    case 'manufacturer_part_number':
                        $item['ProductListingDetails']['BrandMPN']['MPN'] = $optAttr['magento_attribute_code'] =='default' ? $optAttr['default'] : $product->getData($optAttr['magento_attribute_code']);
                        break;
                    case 'auto_pay':
                        $item['AutoPay'] = $optAttr['magento_attribute_code'] =='default' ? $optAttr['default'] : $product->getData($optAttr['magento_attribute_code']);
                        break;
                        default:
                            break;
                    }
                }
            }
            if (isset($item['ProductListingDetails']['BrandMPN']['Brand']) && !isset($item['ProductListingDetails']['BrandMPN']['MPN'])) {
                $item['ProductListingDetails']['BrandMPN']['MPN'] = "Does Not Apply";
            }
            if ($error) {
                $item['type'] = "error";
                $item['data'] = $msg;
            }
        } catch (\Exception $e) {
            $this->logger->addError('In Prepare Data: Product SKU:'.$product->getSku().' has exception '.$e->getMessage(), ['path' => __METHOD__]);
            $item['type'] = "error";
            $item['data'] = 'Product SKU:'.$product->getSku().' has exception '.$e->getMessage();
        }
        return $item;
    }

    /**
     * @param $product
     * @return string
     */
    /*public function createCustomOption($product)
    {
        $stockItem = $this->objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
        $stock = $stockItem->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $valuelist ='';
        $customOptions = $this->objectManager->get('Magento\Catalog\Model\Product\Option')->getProductOptionCollection($product);
        foreach($customOptions as $option) {
            $values = '';
            $varSpecificsSet = '<NameValueList>
                                  <Name>'.$option->getTitle().'</Name>';
            $optionval = $option->getValues();
            foreach($optionval as $value) {
                $values .= '<Value>'.$value->getTitle().'</Value>';
            }
            $valuelist .= $varSpecificsSet.
                        $values.
                    '</NameValueList>';
        }
        $varSpecSetArray = !empty($valuelist) ? '<VariationSpecificsSet>'.$valuelist.'</VariationSpecificsSet>' : '';
        $variations = '';
        foreach($customOptions as $option) {
            $optionval = $option->getValues();
            foreach($optionval as $value) {
                $price = floatval($value->getPrice()) + floatval($this->getGXpressPrice($product));
                $variations .=   '<Variation>
                                <Quantity>'.$stock->getQty().'</Quantity>
                                <VariationSpecifics>
                                    <NameValueList>
                                        <Name>'.$option->getTitle().$value->getOptionTypeId().'</Name>
                                        <Value>'.$value->getTitle().'</Value>
                                    </NameValueList>
                                </VariationSpecifics>
                            </Variation>';
            }
        }
        $finalXml = !empty($variations) ? '<Variations>'.$varSpecSetArray.$variations.'</Variations>' : '';
        return $finalXml;
    }*/
    
    /**
     * @param $product
     * @return string
     */
    public function createConfigProduct($product)
    {
        $valuelist =array();
        $productType = $product->getTypeInstance();
        $attrs = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
        foreach ($attrs as $attr) {
            $values = array();
            foreach ($attr['values'] as $attrValues) {
                $values[] = $attrValues['value_index'];
            }
            $valuelist[$attr['attribute_code']] = $values;
        }
        return $valuelist;
    }

    /**
     * @param $productId
     * @return array
     */
    /*public function getInventoryPrice($productId)
    {
        try {
            $accountId = 0;
            $account = $this->_coreRegistry->registry('gxpress_account');
            if($account) {
                $accountId = $account->getId();
            }
            $itemIdAccAttr = $this->multiAccountHelper->getItemIdAttrForAcc($accountId);
            $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($productId);
            $gxpressItemId = $product->getData($itemIdAccAttr);
            $price = $this->getGXpressPrice($product);
            $sku = $product->getSku();
            $stockItem = $this->objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
            $stock = $stockItem->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
            $qty = $stock->getQty();
            if (!empty($price) && !empty($gxpressItemId) && !empty($sku)) {
                $finalXml = '<InventoryStatus>
                                <SKU>' . $sku . '</SKU>
                                <ItemID>' . $gxpressItemId . '</ItemID>
                                <StartPrice>' . $price . '</StartPrice>
                                <Quantity>' . $qty . '</Quantity>
                              </InventoryStatus>';

                $content = [
                    'type' => 'success',
                    'data' => $finalXml
                ];
            } else {
                $content = [
                    'type' => 'error',
                    'data' => 'please check Price or Inventory for: ' . $product->getSku()
                ];
            }
        } catch (\Exception $e) {
            $this->logger->addError('In Inventory Sync: has exception '.$e->getMessage(), ['path' => __METHOD__]);
            $content = [
                'type' => 'error',
                'data' => $e->getMessage()
            ];
        }
        return $content;
    }*/

    /**
     * @param $product
     * @return array
     */
    /*public function endListing($product, $type=null)
    {
        try {
            $accountId = 0;
            $account = $this->_coreRegistry->registry('gxpress_account');
            if($account) {
                $accountId = $account->getId();
            }
            $itemIdAccAttr = $this->multiAccountHelper->getItemIdAttrForAcc($accountId);
            if (is_string($product)) {
                $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($product);
            }
            $gxpressItemId = $product->getData($itemIdAccAttr);
            $listingType = $product->getListingType();
            $productId = $product->getEntityId();
            $message = $this->scopeConfig->getValue('gxpress_config/product_upload/end_listing_reason');
            if ($message != '') {
                if ($listingType == 'Chinese') {
                    $message = 'SellToHighBidder';
                }
                if ($type) {
                    $finalXml = '<EndItemRequestContainer>
                                    <MessageID>' . $productId . '</MessageID>
                                    <EndingReason>' . $message . '</EndingReason>
                                    <ItemID>' . $gxpressItemId . '</ItemID>
                                    </EndItemRequestContainer>';
                } else {
                    $finalXml = '<MessageID>' . $productId . '</MessageID>
                                <EndingReason>' . $message . '</EndingReason>
                                <ItemID>' . $gxpressItemId . '</ItemID>';
                }

                $content = [
                    'type' => 'success',
                    'data' => $finalXml
                ];
            } else {
                $content = [
                    'type' => 'error',
                    'data' => "please fill the end listing reason in configuration"
                ];
            }
        } catch (\Exception $e) {
            $this->logger->addError('In End Listing: has exception '.$e->getMessage(), ['path' => __METHOD__]);
            $content = [
                'type' => 'error',
                'data' => $e->getMessage()
            ];
        }
        return $content;
    }*/

    /**
     * @param $product
     * @return float|null
     */

    public function getGXpressPrice($product)
    {
        $price = (float)$product->getFinalPrice();
        $configPrice = trim(
            $this->scopeConfig->getvalue(
                'gxpress_config/product_upload/product_price'
            )
        );

        switch ($configPrice) {
        case 'plus_fixed':
            $fixedPrice = trim(
                $this->scopeConfig->getvalue(
                    'gxpress_config/product_upload/fix_price'
                )
            );
            $price = $this->forFixPrice($price, $fixedPrice, 'plus_fixed');
            break;

        case 'plus_per':
            $percentPrice = trim(
                $this->scopeConfig->getvalue(
                    'gxpress_config/product_upload/percentage_price'
                )
            );
            $price = $this->forPerPrice($price, $percentPrice, 'plus_per');
            break;

        case 'min_fixed':
            $fixedPrice = trim(
                $this->scopeConfig->getvalue(
                    'gxpress_config/product_upload/fix_price'
                )
            );
            $price = $this->forFixPrice($price, $fixedPrice, 'min_fixed');
            break;

        case 'min_per':
            $percentPrice = trim(
                $this->scopeConfig->getvalue(
                    'gxpress_config/product_upload/percentage_price'
                )
            );
            $price = $this->forPerPrice($price, $percentPrice, 'min_per');
            break;

        case 'differ':
            $customPriceAttr = trim(
                $this->scopeConfig->getvalue(
                    'gxpress_config/product_upload/different_price'
                )
            );
            try {
                $cprice = (float)$product->getData($customPriceAttr);
            } catch (\Exception $e) {
                $this->getResponse()->setBody($e->getMessage());
            }
            $price = ($cprice != 0) ? $cprice : $price;
            break;

        default:
            return (float)$price;

        }
        return $price;
    }

    /**
     * @param null $price
     * @param null $fixedPrice
     * @param $configPrice
     * @return float|null
     */
    public function forFixPrice($price = null, $fixedPrice = null, $configPrice)
    {
        if (is_numeric($fixedPrice) && ($fixedPrice != '')) {
            $fixedPrice = (float)$fixedPrice;
            if ($fixedPrice > 0) {
                $price = $configPrice == 'plus_fixed' ? (float)($price + $fixedPrice)
                    : (float)($price - $fixedPrice);
            }
        }
        return $price;
    }

    /**
     * @param null $price
     * @param null $percentPrice
     * @param $configPrice
     * @return float|null
     */
    public function forPerPrice($price = null, $percentPrice = null, $configPrice)
    {
        if (is_numeric($percentPrice)) {
            $percentPrice = (float)$percentPrice;
            if ($percentPrice > 0) {
                $price = $configPrice == 'plus_per' ?
                    (float)($price + (($price / 100) * $percentPrice))
                    : (float)($price - (($price / 100) * $percentPrice));
            }
        }
        return $price;
    }

    /*public function getQuantityForUpload($product)
    {
        $quantity = 0;
        if($product != null) {
            $profileId = $product->getGXpressProfileId();
            $profileData = $this->objectManager->get('Ced\GXpress\Model\Profile')->load($profileId);
            if($profileData->getId() == null) {
                $productParents = $this->objectManager
                        ->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
                        ->getParentIdsByChild($product->getId());
                foreach ($productParents as $parentId) {
                    $parentProduct = $this->objectManager->create('Magento\Catalog\Model\Product')->load($parentId);
                    $profileId = $parentProduct->getGXpressProfileId();
                    if($profileId != null) {
                        $profileData = $this->objectManager->get('Ced\GXpress\Model\Profile')->load($profileId);
                        if($profileData->getId() > 0) {
                            break;
                        }
                    }
                }
            }
            if($profileData->getId() > 0) {
                $reqOptAttr = $profileData->getProfileReqOptAttribute();
                $attributes = json_decode($reqOptAttr, true);
                $attributes = isset($reqOptAttr['required_attributes']) ? array_column($reqOptAttr['required_attributes'], 'gxpress_attribute_name'): [];

                if (is_array($attributes) && isset($attributes['inventory'])) {
                    $quantity = ($attributes['inventory']['magento_attribute_code'] == "default") ? $attributes['inventory']['default'] : $product->getData($attributes['inventory']['magento_attribute_code']);
                    $quantity = isset($quantity['qty']) ? (int)$quantity['qty'] : (int)$quantity;
                } else {
                    $stockItem = $this->objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
                    $stock = $stockItem->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
                    $quantity = (int)$stock->getQty();
                }
            } else {
                $stockItem = $this->objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
                $stock = $stockItem->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
                $quantity = (int)$stock->getQty();
            }
        }
        return $quantity;
    }*/
    
    /*public function array2XML($xml_obj, $array)
    {
        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $key = $key;
            }
            if (is_array($value)) {
                $node = $xml_obj->addChild($key);
                $this->array2XML($node, $value);
            } else {
                $xml_obj->addChild($key, htmlspecialchars($value));
            }
        }
    }*/

    public function getDescriptionTemplate($product, $value=null)
    {
        preg_match_all("/\##(.*?)\##/", $value, $matches);
        foreach (array_unique($matches[1]) as $attrId) {
            $attrValue = $product->getData($attrId);
            $value = str_replace('##'.$attrId.'##', $attrValue, $value);
        }
        $description = '<![CDATA['.$value.']]>';
        return $description;
    }

    /*public function prepareHeader($value)
    {
        $xmlHeader = '<?xml version="1.0" encoding="utf-8"?>';
        switch ($value) {
            case 'AddItem':
                $xmlHeader .= '
                            <AddItemRequest xmlns="urn:gxpress:apis:eBLBaseComponents">';
                break;
            case 'AddFixedPriceItem':
                $xmlHeader .= '
                            <AddFixedPriceItemRequest xmlns="urn:gxpress:apis:eBLBaseComponents">';
                break;
            case 'ReviseItem':
                $xmlHeader .= '
                            <ReviseItemRequest xmlns="urn:gxpress:apis:eBLBaseComponents">';
                break;
            case 'ReviseFixedPriceItem':
                $xmlHeader .= '
                            <ReviseFixedPriceItemRequest xmlns="urn:gxpress:apis:eBLBaseComponents">';
                break;
            case 'AddItems':
                $xmlHeader .= '
                            <AddItemsRequest xmlns="urn:gxpress:apis:eBLBaseComponents">';
                break;
            case 'RelistItem':
                $xmlHeader .= '
                            <RelistItemRequest xmlns="urn:gxpress:apis:eBLBaseComponents">';
                break;
            case 'EndItem':
                $xmlHeader .= '
                            <EndItemRequest xmlns="urn:gxpress:apis:eBLBaseComponents">';
                break;
            case 'EndItems':
                $xmlHeader .= '
                            <EndItemsRequest xmlns="urn:gxpress:apis:eBLBaseComponents">';
                break;
            case 'ReviseInventoryStatus':
                $xmlHeader .= '
                            <ReviseInventoryStatusRequest xmlns="urn:gxpress:apis:eBLBaseComponents">';
                break;
            default:
                break;

        }
        $xmlHeader .= '
                                    <RequesterCredentials>
                                        <gxpressAuthToken>' . $this->token . '</gxpressAuthToken>
                                    </RequesterCredentials>
                                    <Version>989</Version>
                                    <ErrorLanguage>en_US</ErrorLanguage>
                                    <WarningLevel>High</WarningLevel>';

        return $xmlHeader;
    }*/

    public function getConfigData($fieldToRetrieve, $profileConfig = null) {
        $configData = '';
        if($profileConfig) {
            $paymentDetails = $profileConfig->getPaymentDetails();
            $shippingDetails = $profileConfig->getShippingDetails();
            $returnPolicy = $profileConfig->getReturnPolicy();
            if($paymentDetails && $paymentDetails != null) {
                $paymentDetails = $this->json->jsonDecode($paymentDetails);
                if(isset($paymentDetails[$fieldToRetrieve]))
                    $configData = $paymentDetails[$fieldToRetrieve];
            }
            if($shippingDetails && $shippingDetails != null) {
                $shippingDetails = $this->json->jsonDecode($shippingDetails);
                if(isset($shippingDetails[$fieldToRetrieve]))
                    $configData = $shippingDetails[$fieldToRetrieve];
            }
            if($returnPolicy && $returnPolicy != null) {
                $returnPolicy = $this->json->jsonDecode($returnPolicy);
                if(isset($returnPolicy[$fieldToRetrieve]))
                    $configData = $returnPolicy[$fieldToRetrieve];
            }
        }
        return $configData;
    }
}