<?php


namespace Ced\TradeMe\Helper;

use Ced\TradeMe\Model\ProfileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item;
use Magento\Store\Model\StoreManagerInterface;
use Ced\TradeMe\Helper\Logger;


class TradeMe extends \Magento\Framework\App\Helper\AbstractHelper
{
    const API_URL_SANDBOX = "https://api.tmsandbox.co.nz/v1/";
    const API_URL = "https://api.trademe.co.nz/v1/";

    const OAUTH_REQUEST_TOKEN_URL_SANDBOX = "https://secure.tmsandbox.co.nz/Oauth/RequestToken"; //?scope=MyTradeMeRead,
    const OAUTH_REQUEST_TOKEN_URL = "https://secure.trademe.co.nz/Oauth/RequestToken"; //?scope=MyTradeMeRead,
    //MyTradeMeWrite

    const OAUTH_AUTHORISE_TOKEN_URL_SANDBOX = "https://secure.tmsandbox.co.nz/Oauth/Authorize";//?oauth_token=  also get
    // oauth_verifier
    const OAUTH_AUTHORISE_TOKEN_URL = "https://secure.trademe.co.nz/Oauth/Authorize";//?oauth_token=  also get
    // oauth_verifier

    const OAUTH_ACCESS_TOKEN_URL_SANDBOX = "https://secure.tmsandbox.co.nz/Oauth/AccessToken";
    const OAUTH_ACCESS_TOKEN_URL = "https://secure.trademe.co.nz/Oauth/AccessToken";
    protected $scopeConfig;
    public $apiMode;
    public $apiUrl;

    public $fileIo;
    public $_allowedFeedType = array();

    /**
     * @var mixed
     */
    public $permissions;

    /**
     * @var mixed
     */
    public $oauthCallback;

    /**
     * @var mixed
     */
    public $oauthConsumerKey;

    /**
     * @var mixed
     */
    public $oauthConsumerSecret;

    /**
     * @var mixed
     */
    public $oauthToken;

    /**
     * @var mixed
     */
    public $oauthTokenSecret;

    /**
     * @var string
     */
    public $requestTokenUrl;

    /**
     * @var string
     */
    public $authoriseTokenUrl;

    /**
     * @var string
     */
    public $accessTokenUrl;

    /**
     * @var array
     */
    public $authParams;
    public $directoryList;
    public $json;
    public $adminSession;
    public $multiAccountHelper;
    /**
     * @var mixed
     */
    public $paymentMethods;

    /**
     * @var mixed
     */
    public $shippingType;

    /**
     * @var mixed
     */
    public $shippingPrice;

    /**
     * @var mixed
     */
    public $shippingMethod;

    /**
     * @var
     */
    public $priceType;
    public $logger;

    /**
     * @var bool
     */
    public $trademeQty;
    public $messageManager;
    protected $_storeManager;
    public $profileFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Backend\Model\Session $session,
        \Ced\TradeMe\Model\ProfileFactory $profileFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\Manager $manager,
        \Ced\TradeMe\Helper\MultiAccount $multiAccountHelper,
        \Ced\TradeMe\Helper\Data $data,
        \Magento\Framework\Registry $registry,
        StoreManagerInterface $_storeManager,
        Logger $logger,
        DirectoryList $directoryList
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->directoryList = $directoryList;
        $this->json = $json;
        $this->adminSession = $session;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->_coreRegistry = $registry;
        $this->_storeManager = $_storeManager;
        $this->profileFactory = $profileFactory;
        $this->logger = $logger;
        $this->messageManager = $manager;
        $this->objectManager = $objectManager;
        $this->data = $data;
        $this->fileIo = new \Magento\Framework\Filesystem\Io\File();
        $this->apiMode = $this->scopeConfig->getValue('trademe_config/account_setting/mode');
        $this->permissions = $this->scopeConfig->getValue('trademe_config/account_setting/permissions');
        $this->oauthCallback = $this->scopeConfig->getValue('trademe_config/account_setting/oauth_callback');
        $this->oauthConsumerKey = $this->scopeConfig->getValue('trademe_config/account_setting/oauth_consumer_key');
        $this->oauthConsumerSecret = $this->scopeConfig->getValue('trademe_config/account_setting/oauth_consumer_secret');
        $this->oauthToken = $this->scopeConfig->getValue('trademe_config/account_setting/oauth_token');
        $this->oauthTokenSecret = $this->scopeConfig->getValue('trademe_config/account_setting/oauth_token_secret');
        $this->apiUrl = $this->apiMode == 'sandbox' ? self::API_URL_SANDBOX : self::API_URL;
        $this->accessTokenUrl = $this->apiMode == 'sandbox' ? self::OAUTH_ACCESS_TOKEN_URL_SANDBOX
            : self::OAUTH_ACCESS_TOKEN_URL;
        $this->paymentMethods = $this->scopeConfig->getValue('trademe_config/product_upload/payment_methods');
        $this->shippingType = $this->scopeConfig->getValue('trademe_config/product_upload/shipping_type');
        $this->shippingPrice = $this->scopeConfig->getValue('trademe_config/product_upload/shipping_price');
        $this->shippingMethod = $this->scopeConfig->getValue('trademe_config/product_upload/shipping_method');
        $this->priceType = $this->scopeConfig->getValue('trademe_config/product_upload/price_type');
        $this->trademeQty = $this->scopeConfig->getValue('trademe_config/product_upload/trademe_qty');
    }

    public function prepareData($product)
    {
        try {
            $data = array();
            $error = array();
            if ($this->shippingType == 'custom' && (empty($this->shippingPrice) || empty($this->shippingMethod))) {
                $error[] = "Please fill Shipping Price and Shipping Method for 'custom' type shipping";
            }
            $account = $this->_coreRegistry->registry('trademe_account');
            $profileIdAccAttr = $this->multiAccountHelper->getProfileAttrForAcc($account->getId());
            $profileId = $product->getData($profileIdAccAttr);

            $profile = $this->profileFactory->create()->load($profileId);
            // prepare required data
            $data['Category'] = $profile->getProfileCategory();
            $data['ReservePrice'] = $product->getFinalPrice();
            $trademePrice = $this->getTrademePrice($product);
            $data['BuyNowPrice'] = (int)$trademePrice['splprice'];
            $data['isBrandNew'] = "true";

            // prepare required and optional attribute data
            $reqOptAttributes = $this->getReqOptAttributes($product, $profile);
//            print_r($product->getEntityId());
//            print_r($reqOptAttributes);die;
            if (isset($reqOptAttributes['error'])) {
                $error[] = $reqOptAttributes['error'];
                unset($reqOptAttributes['error']);
            }
            if (isset($reqOptAttributes['notice'])) {
                $this->messageManager->addSuccess($reqOptAttributes['notice']);
                unset($reqOptAttributes['notice']);
            }
            $data = array_merge_recursive($data, $reqOptAttributes);
            // get price
            if ($data['BuyNowPrice'] < (int)$data['StartPrice']) {
                $error[] = "StartPrice should be equal or less than BuyNowPrice. Currently BuyNowPrice is " . $data['BuyNowPrice'] . " and StartPrice is " . $data['StartPrice'];
            }
            $data['IsBrandNew'] = 'true';
//           unset($data['BuyNowPrice']);
//            print_r($data);die;

            // get inventory
            if (isset($data['inventory']['is_in_stock']) && $data['inventory']['is_in_stock']  == 0) {
                $error[] = $product->getSku() . " is out of stock";
            }
            $stockState = $stock = $this->objectManager->create('Magento\CatalogInventory\Api\StockRegistryInterface');
            $stock = $stockState->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
            $qty = (int) $stock->getQty();
            $data['Quantity'] = $qty;

            // get shipping-payment option
            $shipOptions = array();
            $shipType = explode(',', $this->shippingType);
            foreach ($shipType as $type) {
                $temp = array();
                $temp['Type'] = (int)$type;
                if ($type == 4) {
                    if (!empty($this->shippingPrice) && !empty($this->shippingMethod)) {
                        $temp['Price'] = (int)$this->shippingPrice;
                        $temp['Method'] = $this->shippingMethod;
                    } else {
                        $error[] = "shipping price and shipping method for Custom Type Shipping Type are required";
                    }
                }
                $shipOptions[/*'ShippingOption'*/] = $temp;
            }
            $data['ShippingOptions'] = $shipOptions;
            $data['ShippingOptions'][1] = ['Type' => 4, 'Price' => '13.50', 'Method' => 'EXPRESS SHIPPING'];
            $data['PaymentMethods'] = explode(',', $this->paymentMethods);
            // prepare category dependent attribute data
            $catDependentAttributes = $this->getCatDependentAttributes($product, $profile);
            if (isset($catDependentAttributes['error'])) {
                $error[] = $catDependentAttributes['error'];
                unset($catDependentAttributes['error']);
            }
            if (isset($catDependentAttributes['notice'])) {
                $this->messageManager->addSuccess($catDependentAttributes['notice']);
                unset($catDependentAttributes['notice']);
            }
            $data = array_merge_recursive($data, $catDependentAttributes);
            //for sync product on trademe
            $listingId = $this->multiAccountHelper->getProdListingIdAttrForAcc($account->getId());
            if ($product->getData($listingId)) {
                $data['ListingId'] = $product->getData($listingId);
            }

            // for config product data prepare
            if ($product->getTypeId() == 'configurable') {
                $configData = $this->prepareConfigData($product, $account->getId());
                if (isset($configData['error'])) {
                    $error[] = $configData['error'];
                    unset($configData['error']);
                }
                if (isset($configData['notice'])) {
                    $this->messageManager->addSuccess($configData['notice']);
                    unset($configData['notice']);
                }
                $data = array_merge_recursive($data, $configData);
            }

            $response['data'] = $data;
            if (!empty($error)) {
                $response['error'] = implode(',', $error);
            }
            unset($response['data']['inventory']);
            return $response;
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
            return $response;

        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param $accountId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function imageData($product, $accountId)
    {
        $PhotoId = array();
        $photoIdAttr = $this->multiAccountHelper->getProdPhotoIdAttrForAcc($accountId);
        $images = $product->getMediaGalleryImages();
        $pPhotoId = $product->getData($photoIdAttr);
        if (!empty($pPhotoId)){
            $pPhotoId = explode(',', $pPhotoId);
        }
        else
            $pPhotoId = [];


        if (count($pPhotoId) != $images->getSize()) {
            /** @var \Magento\Framework\Data\Collection $images */

            /**
             * @var  $key
             * @var \Magento\Framework\DataObject $image
             */
            foreach ($images as $key => $image) {

                $nam = explode('/', $image->getData('file'));
                $imagestring = explode('.', $image->getData('file'));
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');

                $media = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
//                print_r($image->getData('file'));
//                echo "<br/>";
                $url = $media . 'catalog/product' . $image->getData('file');
                $request = curl_init();
                curl_setopt($request, CURLOPT_URL, $url);
                curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($request, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($request);
                $errors = curl_error($request);
                curl_close($request);
                $photoData['PhotoData'] = base64_encode($response);
                $photoData['FileType'] = strtoupper($imagestring[1]);
                $photoData['FileName'] = $nam[3];
                $response = $this->data->imageUpload($photoData);
                if ((isset($response['Status'])) && ($response['Status'] == '1')) {
                    $PhotoId['success'][] = $response['PhotoId'];
                }
                if (isset($response['ErrorDescription'])) {
                    $PhotoId['error'] = $response['ErrorDescription'];
                }
            }
//            die('ff');

            if (isset($PhotoId['success'])) {
                $this->saveResponseOnProduct($PhotoId, $product);
            }
        } else {
            $PhotoId['success'] = $pPhotoId;
        }
        return $PhotoId;
    }

    public function getReqOptAttributes($product, $profile)
    {
        try {
            $data = array();
            $error = array();
            $notice = array();
            $values = '';
            $reqOptAttributes = json_decode($profile->getOptReqAttribute(), true);

            if (is_array($reqOptAttributes['required_attributes'])){
                foreach ($reqOptAttributes['required_attributes'] as $key => $value) {
                    if ($value/*['_value']*/ ['magento_attribute_code'] != 'default') {

                        if ($product->getData($value/*['_value']*/ ['magento_attribute_code']) == '') {
                            if ($product->getTypeId() == 'configurable' && $value['magento_attribute_code'] == 'price'){
                                $childProds = $product->getTypeInstance()->getUsedProducts($product);
                                $price = 0;
                                foreach ($childProds as $childProd) {
                                    $prodPrice = $this->getTrademePrice($childProd);
                                    if ($price < $prodPrice['price'])
                                        $price = $prodPrice['price'];
                                }
                                $data[$value['trademe_attribute_name']] = $price;

                            } else if ($product->getTypeId() == 'configurable' && $value['magento_attribute_code'] == 'special_price'){
                                $childProds = $product->getTypeInstance()->getUsedProducts($product);
                                $price = 0;
                                foreach ($childProds as $childProd) {
                                    $prodPrice = $this->getTrademePrice($childProd);
                                    if ($price < $prodPrice['splprice'])
                                        $price = $prodPrice['splprice'];
                                }
                                $data[$value['trademe_attribute_name']] = $price;

                            }else {
                                $error[] = $value/*['_value']*/
                                    ['magento_attribute_code'] . ' value cannot be empty';
                            }

                        } else {

                            switch ($value['trademe_attribute_name']) {
                                case 'Title':
                                    $title = $product->getData($value/*['_value']*/ ['magento_attribute_code']);
                                    $data[$value['trademe_attribute_name']] = substr($title, 0, 80);
                                    continue;

                                case 'Description':
                                    $description = $product->getData($value/*['_value']*/ ['magento_attribute_code']);
                                    $data[$value['trademe_attribute_name']] = array(substr(strip_tags($description), 0, 2048));
                                    continue;

                                default:
                                    $data[$value['trademe_attribute_name']] = $product->getData($value/*['_value']*/ ['magento_attribute_code']);
                                    continue;
                            }
                        }

                    } else if (isset($value/*['_value']*/ ['default']) && $value/*['_value']*/ ['default'] != '') {
                        if ($value['trademe_attribute_name'] == 'Description') {
                            $value = $this->getDescriptionTemplate($product, $value/*['_value']*/ ['default']);
                        } else {
                            $values = $value/*['_value']*/
                            ['default'];
                        }
                        $data[$value['trademe_attribute_name']] = $values;
                    } else {
                        $error[] = 'set the default value' . $value/*['_value']*/
                            ['magento_attribute_code'];
                    }
                }
            }else{
                $error[] = 'Profile Mapping : '.$profile->getOptReqAttribute();
            }

            if (is_array($reqOptAttributes['optional_attributes'])) {
                foreach ($reqOptAttributes['optional_attributes'] as $key => $value) {
                    if ($value/*['_value']*/['magento_attribute_code'] != 'default') {
                        if ($product->getData($value/*['_value']*/ ['magento_attribute_code']) == '') {
                            $notice[] = $value/*['_value']*/
                                ['magento_attribute_code'] . ' value cannot be empty';
                        } else {
                            $attrVal = $product->getData($value/*['_value']*/ ['magento_attribute_code']);
                            switch ($key) {
                                case 'Tags':
                                    if (!empty($attrVal)) {
                                        $tagsArray = explode(',', $attrVal);
                                        if (count($tagsArray) > 1) {
                                            foreach ($tagsArray as $tag) {
                                                $data['AdditionalData']['Tags'][] = array('Name' => $tag);
                                            }
                                        } else {
                                            $data['AdditionalData']['Tags'] = array('Name' => $tagsArray);
                                        }
                                    }
                                    continue;

                                case 'Subtitle':
                                    $data[$key] = substr($attrVal, 0, 50);
                                    continue;

                                case 'BulletPoints':
                                    if (!empty($attrVal)) {
                                        preg_match_all("/\{(.*?)\}/", $attrVal, $matches);
                                        $new_bullets = array();
                                        $new_bullets = $matches[1];
                                        $data['AdditionalData']['BulletPoints'] = $new_bullets;
                                    }
                                    continue;

                                default:
                                    $data[$key] = $attrVal;
                                    continue;
                            }
                        }
                    } else if (isset($value/*['_value']*/ ['default']) && $value/*['_value']*/ ['default'] != '') {
                        $data[$key] = $value['_value']['default'];
                    } else {
                        $notice[] = 'set the default value' . $value/*['_value']*/
                            ['magento_attribute_code'];
                    }
                }

            }
            if (!empty($error)) {
                $data['error'] = implode(',', $error);
            }

            if (!empty($notice)) {
                $data['notice'] = implode(',', $notice);
            }
        } catch (\Exception $e) {
            $data['error'] = $e->getMessage();
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);

        }
        return $data;
    }

    public function getDescriptionTemplate($product, $value = null)
    {
        preg_match_all("/\##(.*?)\##/", $value, $matches);
        foreach (array_unique($matches[1]) as $attrId) {
            $attrValue = $product->getData($attrId);
            $value = str_replace('##' . $attrId . '##', $attrValue, $value);
        }
        $description = array(substr(strip_tags($value), 0, 2048));
        return $description;
    }

    public function getCatDependentAttributes($product, $profile)
    {
        try {
            $data = array();
            $data1 = $data2 = [];
            $error = array();
            $notice = array();
            $catDependAttributes['required_attributes'] = array();
            $catDependAttributes['optional_attributes'] = array();
            $catDependAttributes = json_decode($profile->getCatDependAttribute(), true);
            if (isset($catDependAttributes['required_attributes'])) {
                foreach ($catDependAttributes['required_attributes'] as $key => $value) {
                    $temp = array();
                    if ($value/*['_value']*/ ['magento_attribute_code'] != 'default') {
                        if ($product->getData($value/*['_value']*/ ['magento_attribute_code']) == '') {
                            $error[] = $value/*['_value']*/
                                ['magento_attribute_code'] . ' value cannot be empty';
                        } else {
                            $temp['Name'] = $value['trademe_attribute_name'];
                            $temp['Value'] = $product->getData($value/*['_value']*/ ['magento_attribute_code']);
                            $temp['DisplayName'] = $value['trademe_attribute_name'];
                        }
                    } else if (isset($value/*['_value']*/ ['default']) && $value/*['_value']*/ ['default'] != '') {
                        $temp['Name'] = $value['trademe_attribute_name'];
                        $temp['Value'] = $value/*['_value']*/
                        ['default'];
                        $temp['DisplayName'] = $value/*['_value']*/
                        ['trademe_attribute_name'];
                    } else {
                        $error[] = 'set the default value' . $value/*['_value']*/
                            ['magento_attribute_code'];
                    }
                    $data1[] = !empty($temp) ? $temp : array();
                }
        }

            if (isset($catDependAttributes['optional_attributes'])) {
                foreach ($catDependAttributes['optional_attributes'] as $key => $value) {
                    if ($value/*['_value']*/ ['magento_attribute_code'] != 'default') {
                        if ($product->getData($value/*['_value']*/ ['magento_attribute_code']) == '') {
                            $notice[] = $value/*['_value']*/
                                ['magento_attribute_code'] . ' value cannot be empty';
                        } else {
                            $temp['Name'] = $value['trademe_attribute_name'];
                            $temp['Value'] = $product->getData($value/*['_value']*/ ['magento_attribute_code']);
                            $temp['DisplayName'] = $value['trademe_attribute_name'];
                        }
                    } else if (isset($value/*['_value']*/ ['default']) && $value/*['_value']*/ ['default'] != '') {
                        $temp['Name'] = $value['trademe_attribute_name'];
                        $temp['Value'] = $value/*['_value']*/
                        ['default'];
                        $temp['DisplayName'] = $value['trademe_attribute_name'];
                    } else {
                        $notice[] = 'set the default value' . $value/*['_value']*/
                            ['magento_attribute_code'];
                    }
                    $data2[] = !empty($temp) ? $temp : array();
                }
            }
            if (!empty($data1) || !empty($data2)) {
                $data['Attributes'] = array_merge_recursive($data1, $data2);
            }
            if (!empty($error)) {
                $data['error'] = implode(',', $error);
            }

            if (!empty($notice)) {
                $data['notice'] = implode(',', $notice);
            }
        } catch (\Exception $e) {
            $this->logger->addError('In Category Dependent Attribute: has exception '.$e->getMessage(), ['path' => __METHOD__]);
        }
        return $data;
    }

    public function prepareConfigData($product, $accId)
    {
        try {
            /** @var  $product Mage_Catalog_Model_Product */
            $optionSets = array();
            $variants = array();
            $values = array();
            $varProducts = $product->getTypeInstance()->getUsedProducts($product);
            $configAttr = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
            foreach ($configAttr as $attr) {
                $values = array();
                $tempArray = array();
                $tempArray["Name"] = $attr['label'];
                foreach ($attr['values'] as $attrValues) {
                    $values[] = $attrValues['label'];
                }
                $tempArray["Values"] = $values;
                $optionSets[] = $tempArray;
            }
            $attrs = $product->getTypeInstance(true)->getConfigurableAttributes($product);
            $basePrice = $product->getFinalPrice();
//            print_r($basePrice);die;
            foreach ($varProducts as $varProduct) {
                $childProd = $this->objectManager->create('Magento\Catalog\Model\Product')
                    ->loadByAttribute('entity_id', $varProduct->getEntityId());
                $basePrice = $childProd->getFinalPrice();
                $totalPrice = $basePrice;
                $tempArray = array();
                /** @var $varProduct Mage_Catalog_Model_Product */
                $tempArray['SKU'] = $varProduct->getSku();
                $stockState = $stock = $this->objectManager->create('Magento\CatalogInventory\Api\StockRegistryInterface');
                $stock = $stockState->getStockItem($varProduct->getId(), $varProduct->getStore()->getWebsiteId());
                $qty = (int) $stock->getQty();
                $tempArray['Quantity'] = $qty;
                foreach ($attrs as $attr) {
                    $prices = $attr->getPrices();
                    if (!empty($prices)) {
                        foreach ($prices as $price) {
                            if ($price['is_percent']) { //if the price is specified in percents
                                $pricesByAttributeValues[$price['value_index']] = (float)$price['pricing_value'] * $basePrice / 100;
                            } else { //if the price is absolute value
                                $pricesByAttributeValues[$price['value_index']] = (float)$price['pricing_value'];
                            }
                        }
                    }
                    $value = $varProduct->getData($attr->getProductAttribute()->getAttributeCode());
                    $lable = $attr->getLabel();
                    $tempArray['Options'][] = array('Name' => $lable, 'Value' => $varProduct->getAttributeText($attr->getProductAttribute()->getAttributeCode()));
                    if (isset($pricesByAttributeValues[$value])){
                        $totalPrice += $pricesByAttributeValues[$value];
                    }
                }
                $photoId = $this->imageData($varProduct, $accId);
                if (isset($photoId['success']))
                    $tempArray['PhotoIds'] = $photoId['success'];
                $tempArray['Price'] = $totalPrice;
                $prodListingId = $this->multiAccountHelper->getProdListingIdAttrForAcc($accId);
//                print_r($childProd->getData($prodListingId));die;
                if ($childProd->getData($prodListingId))
                    $tempArray['ListingId'] = $childProd->getData($prodListingId);
//                print_r($tempArray);die;
                $variants[] = $tempArray;
            }

            $finalArray['OptionSets'] = $optionSets;
            $finalArray['Variants'] = $variants;
            $data['VariantDefinition'] = $finalArray;
        } catch (\Exception $e) {
            $data['error'] = $e->getMessage();
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
        }
        return $data;
    }

    public function getTrademePrice($productObject)
    {
        try {
            $splprice = (float)$productObject->getFinalPrice();
            $price = (float)$productObject->getPrice();
            $splprice = round($splprice, 4);
            $price = round($price, 4);
            switch ($this->priceType) {
                case 'plus_fixed':

                    $fixedPrice = trim(
                        $this->scopeConfig->getValue(
                            'trademe_config/product_upload/fix_price'
                        )
                    );
                    $price = $this->forFixPrice($price, $fixedPrice, 'plus_fixed');
                    $splprice = $this->forFixPrice($splprice, $fixedPrice, 'plus_fixed');
                    break;

                case 'plus_per':
                    $percentPrice = trim(
                        $this->scopeConfig->getValue(
                            'trademe_config/product_upload/percentage_price'
                        )
                    );
                    $price = $this->forPerPrice($price, $percentPrice, 'plus_per');
                    $splprice = $this->forPerPrice($splprice, $percentPrice, 'plus_per');
                    break;

                case 'min_fixed':
                    $fixedPrice = trim(
                        $this->scopeConfig->getValue(
                            'trademe_config/product_upload/fix_price_min'
                        )
                    );
                    $price = $this->forFixPrice($price, $fixedPrice, 'min_fixed');
                    $splprice = $this->forFixPrice($splprice, $fixedPrice, 'min_fixed');
                    break;

                case 'min_per':
                    $percentPrice = trim(
                        $this->scopeConfig->getValue(
                            'trademe_config/product_upload/percentage_price_min'
                        )
                    );
                    $price = $this->forPerPrice($price, $percentPrice, 'min_per');
                    $splprice = $this->forPerPrice($splprice, $percentPrice, 'min_per');
                    break;

                case 'differ':
                    $customPriceAttr = trim(
                        $this->scopeConfig->getValue(
                            'trademe_config/product_upload/different_price'
                        )
                    );
                    try {
                        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($productObject->getId());
                        $cprice = (float)$product->getData($customPriceAttr);
                    } catch (\Exception $e) {
                        $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);

                    }

                    $price = (isset($cprice) && $cprice != 0) ? $cprice : $price;
                    $splprice = $price;
                    break;

                default:
                    return array(
                        'price' => (string)round($price, 4),
                        'splprice' => (string)round($splprice, 4),
                    );
            }

            return array(
                'price' => (string)round($price, 4),
                'splprice' => (string)round($splprice, 4),
            );
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
            return false;

        }
    }

    public function saveResponseOnProduct($resultData, $product)
    {
        $successids = $message = array();
        $message['error'] = "";
        $message['success'] = "";
        reset($resultData); // make sure array pointer is at first element
        $firstKey = key($resultData);
        $accountId = 0;
        $currentAccount = $this->_coreRegistry->registry('trademe_account');
        if ($currentAccount) {
            $accountId = $currentAccount->getId();
        }
        $prodStatusAccAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($accountId);
        $uploadTime = $this->multiAccountHelper->getUploadTimeAttrForAcc($accountId);
        $listingErrorAccAttr = $this->multiAccountHelper->getProdListingErrorAttrForAcc($accountId);
        if (isset($resultData['error'])) {
            $errors[] =  $resultData['error'];
            $ulLierrorResponse = $this->convertUlLi($errors);
            $listingError = $this->preapareResponse($product->getEntityId(), $firstKey, $product->getSku(), $errors);
//            $product->setData($prodStatusAccAttr, 'not_uploaded');
            $product->setData($listingErrorAccAttr, $listingError);
            $product->getResource()/*->saveAttribute($product, $prodStatusAccAttr)*/->saveAttribute($product, $listingErrorAccAttr);

            $product->setData($prodStatusAccAttr, 'not_uploaded');
            $product->setData($listingErrorAccAttr, $resultData['error'] /*json_encode*//*(["valid"])*/);
            $product->getResource()->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $listingErrorAccAttr);

        } elseif (isset($resultData['ErrorDescription'])){

            $errors[] =  $resultData['ErrorDescription'];
            $ulLierrorResponse = $this->convertUlLi($errors);
            $listingError = $this->preapareResponse($product->getEntityId(), $firstKey, $product->getSku(), $errors);
//            $product->setData($prodStatusAccAttr, 'not_uploaded');
            $product->setData($listingErrorAccAttr, $listingError);
            $product->getResource()/*->saveAttribute($product, $prodStatusAccAttr)*/->saveAttribute($product, $listingErrorAccAttr);

        } elseif (isset($resultData['Success']) && isset($resultData['ListingId'])) {
            $listingId = $this->multiAccountHelper->getProdListingIdAttrForAcc($accountId);
            if ($product->getTypeId() == 'configurable'){
                foreach ($resultData['Variants'] as $variant) {
                    $childProd = $this->objectManager->create('Magento\Catalog\Model\Product')->loadByAttribute('sku', $variant['SKU']);
                    $childProd->setData($listingId, $variant['ListingId']);
                    $childProd->getResource()->saveAttribute($childProd, $listingId);

                }
            }

            $product->setData($prodStatusAccAttr, 'uploaded');
            $product->setData($uploadTime, date("Y-m-d"));
            $product->setData($listingErrorAccAttr, json_encode(["valid"]));
            $product->setData($listingId, $resultData['ListingId']);
            $product->getResource()->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $listingErrorAccAttr)->saveAttribute($product, $listingId)->saveAttribute($product, $uploadTime);
            $successids[] = $product->getSku();
        } elseif (isset($resultData['success']) /*&& isset($resultData['PhotoId'])*/) {
            $trademePhotoId = $this->multiAccountHelper->getProdPhotoIdAttrForAcc($accountId);
            $product->setData($trademePhotoId, implode(',', $resultData['success']));
            $product->getResource()->saveAttribute($product, $trademePhotoId);

        } elseif (isset($resultData['Success']) && isset($resultData['Description'])) {
            $errors[] =  $resultData['Description'];
            $ulLierrorResponse = $this->convertUlLi($errors);
            $listingError = $this->preapareResponse($product->getEntityId(), $firstKey, $product->getSku(), $errors);
            $product->setData($prodStatusAccAttr, 'invalid');
            $product->setData($listingErrorAccAttr, $listingError);
            $product->getResource()->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $listingErrorAccAttr);
        }

    }

    public function convertUlLi($errors)
    {
        $errorMsg = '';
        $errorMsg .= "<br><ul class='all-validation-errors'>";
        foreach ($errors as $error){
            $errorMsg .= "<li>".$error."</li>";
        }
        $errorMsg .="</ul>";
        return $errorMsg;
    }
    public function preapareResponse($id=null, $variable, $sku, $errors)
    {
        if(is_array($errors)) {
            $errors = json_encode($errors);
        }
        $response = [];
        $response[$variable] =
            [
                "id" => $id,
                "sku" => $sku,
                "url" => "#",
                'errors' => $errors
            ];

        return json_encode($response);
    }

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



}
