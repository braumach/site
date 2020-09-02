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

use Magento\Framework\App\Helper\Context;
use Magento\Backend\Model\Session;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Ced\GXpress\Block\Extensions;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Message\Manager;

/**
 * Class Data
 * @package Ced\GXpress\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    CONST CONFIG_PATH_PRODUCT_PRICE = 'gxpress_config/product_upload/product_price';

    const CONFIG_PATH_PRODUCT_PRICE_INCREASE_FIXED = 'gxpress_config/product_upload/fix_price';
    const CONFIG_PATH_PRODUCT_PRICE_INCREASE_PERCENTAGE = 'gxpress_config/product_upload/percentage_price';
    const CONFIG_PATH_PRODUCT_DESTINATION = 'gxpress_config/product_upload/included_destination';
    const CONFIG_PATH_PRODUCT_TARGETCOUNTRY = 'gxpress_config/product_upload/target_country';
    const CONFIG_PATH_PRODUCT_CONTENTLANGUAGE = 'gxpress_config/product_upload/content_language';
    const GXPRESS_DEBUGMODE = 'gxpress_config/product_upload/debugmode';

    /**
     * @var mixed
     */
    public $adminSession;

    /**
     * @var mixed
     */
    public $debugMode;

    /**
     * @var Manager
     */
    public $messageManager;
    /**
     * @var DirectoryList
     */
    public $directoryList;
    /**
     * Json Parser
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $json;
    /**
     * @var int
     */
    public $compatLevel;
    /**
     * @var mixed
     */
    public $siteID;
    /**
     * @var mixed
     */
    public $devID;
    /**
     * @var mixed
     */
    public $environment;
    /**
     * @var mixed
     */
    public $token;
    /**
     * @var mixed
     */
    public $developer;

    /**
     * @var mixed
     */
    public $appId;

    /**
     * @var mixed
     */
    public $certID;
    /**
     * @var mixed
     */
    public $ruNameID;

    /**
     * @var Filesystem
     */
    public $filesystem;

    /**
     * @var Feed
     */
    public $feedHelper;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var Config
     */
    public $configResourceModel;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    public $backendHelper;

    /**
     * @var \Ced\GXpress\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * Data constructor.
     * @param Context $context
     * @param Manager $manager
     * @param DirectoryList $directoryList
     * @param \Magento\Framework\Json\Helper\Data $json
     * @param Session $session
     * @param Filesystem $filesystem
     * @param Feed $feedHelper
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param \Magento\Backend\Helper\Data $helper
     * @param Logger
     */
    public function __construct(
        Context $context,
        Manager $manager,
        DirectoryList $directoryList,
        \Magento\Framework\Json\Helper\Data $json,
        Session $session,
        Filesystem $filesystem,
        Feed $feedHelper,
        StoreManagerInterface $storeManager,
        Config $config,
        \Magento\Framework\UrlInterface $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem\Io\File $fileIo,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Registry $registry,
        \Ced\GXpress\Helper\MultiAccount $multiAccountHelper,
        \Ced\GXpress\Helper\GXpress $GXpress,
        \Ced\GXpress\Helper\Logger $logger
    )
    {
        parent::__construct($context);
        $this->_coreRegistry = $registry;
        $this->messageManager = $manager;
        $this->objectManager = $objectManager;
        $this->directoryList = $directoryList;
        $this->json = $json;
        $this->adminSession = $session;
        $this->configResourceModel = $config;
        $this->backendHelper = $helper;
        $this->filesystem = $filesystem;
        $this->feedHelper = $feedHelper;
        $this->storeManager = $storeManager;
        $this->fileIo = $fileIo;
        $this->dateTime = $dateTime;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->timestamp = (string)$this->dateTime->gmtTimestamp();
        $this->devID = $this->scopeConfig->getValue('gxpress_config/gxpress_setting/dev_id');
        $this->developer = $this->scopeConfig->getValue('gxpress_config/gxpress_setting/dev_acc');
        $this->appId = $this->scopeConfig->getValue('gxpress_config/gxpress_setting/app_id');
        $this->certID = $this->scopeConfig->getValue('gxpress_config/gxpress_setting/cert_id');
        $this->ruNameID = $this->scopeConfig->getValue('gxpress_config/gxpress_setting/ru_name');
        $this->compatLevel = 989;
        $this->GXpress = $GXpress;
        $this->logger = $logger;
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $this->debugMode = $this->scopeConfig->getValue(self::GXPRESS_DEBUGMODE, $storeScope);
    }

    public function updateAccountVariable()
    {
        $account = false;
        if ($this->_coreRegistry->registry('gxpress_account')) {
            $account = $this->_coreRegistry->registry('gxpress_account');
        }
        $this->environment = ($account) ? trim($account->getAccountEnv()) : '';
        $this->token = ($account) ? trim($account->getAccountToken()) : '';
    }


    /**
     * @return $this|bool
     */
    public function checkForLicence()
    {
        if ($this->_request->getModuleName() != 'gxpress') {
            return $this;
        }
        $modules = $this->feedHelper->getCedCommerceExtensions();
        foreach ($modules as $moduleName => $releaseVersion) {
            $m = strtolower($moduleName);
            if (!preg_match('/ced/i', $m)) {
                return $this;
            }

            $h = $this->scopeConfig->getValue(Extensions::HASH_PATH_PREFIX . $m . '_hash');

            for ($i = 1; $i <= (int)$this->scopeConfig->getValue(Extensions::HASH_PATH_PREFIX . $m . '_level'); $i++) {
                $h = base64_decode($h);
            }

            $h = json_decode($h, true);
            if ($moduleName == "Magento2_Ced_GXpress")
                if (is_array($h) && isset($h['domain']) && isset($h['module_name']) && isset($h['license']) && strtolower($h['module_name']) == $m && $h['license'] == $this->scopeConfig->getValue(\Ced\GXpress\Block\Extensions::HASH_PATH_PREFIX . $m)) {
                    return $this;
                } else {
                    return false;
                }
        }
        return $this;
    }


    /**
     * @param $path
     * @param null $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreConfig($path, $storeId = null)
    {
        $store = $this->storeManager->getStore($storeId);
        return $this->scopeConfig->getValue($path, 'store', $store->getCode());
    }

    /**
     * @param $path
     * @param string $code
     * @param string $type
     * @return bool|mixed|string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function loadFile($path, $code = '', $type = '')
    {
        if (!empty($code)) {
            $path = $this->directoryList->getPath($code) . "/" . $path;
        }
        if (file_exists($path)) {
            $pathInfo = pathinfo($path);
            if ($pathInfo['extension'] == 'json') {
                $myfile = fopen($path, "r");
                $data = fread($myfile, filesize($path));
                fclose($myfile);
                if (!empty($data)) {
                    $data = empty($type) ? $this->json->jsonDecode($data) : $data;
                    return $data;
                }
            }
        }
        return false;
    }


    /**
     * @param $ids
     * @param bool $async
     * @param bool $release
     * @return array|bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createProductOnGXpress($ids, $async = false, $release = false)
    {
        $returnData = array();
        $destination = array();
        $destinations = $this->scopeConfig->getValue(self::CONFIG_PATH_PRODUCT_DESTINATION);
        $destinations = explode(",",$destinations);
        foreach ($destinations as $key => $dest) {
            $destination[$key]['destinationName'] = $dest;
            $destination[$key]['intention'] = 'required';
        }
        $response['error'] = array();
        $response['success'] = array();

        if (isset($ids) && count($ids) > 0) {
            $productToUpload = array();
            $key = 1;

            if (isset($ids)) {
                foreach ($ids as $accId => $accountid) {
                    foreach ($accountid as $key => $id) {

                        $this->updateAccountVariable();
                        /** @var Mage_Catalog_Model_Product $product */
                        $product = $this->objectManager->create('Magento\Catalog\Model\Product')
                            ->load($id);

                        $url = array();

                        $profileAccount = $this->multiAccountHelper->getAccountRegistry($accId);
                        $prodStatusAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($accId);
                        $profileAccountAttr = $this->multiAccountHelper->getProfileAttrForAcc($accId);
                        $productValidateAttr = $this->multiAccountHelper->getProdListingErrorAttrForAcc($accId);
                        $profileId = $product->getData($profileAccountAttr);

                        $profile = $this->objectManager->create('Ced\GXpress\Model\Profile')->load($profileId);
                        $profileCategory = $profile->getprofile_category();
                        $profileCategory = json_decode($profileCategory, true);
                        $googleProductCategory = implode(' > ', $profileCategory);
                        $attributeArray = explode(" > ", $googleProductCategory);
                        $catData = $this->objectManager->get('Ced\GXpress\Model\Category')->load($attributeArray[0],
                            'csv_firstlevel_id');
                        $attr = $catData->getData('gxpress_attributes');
                        $attr = explode(",", $attr);
                        $attrData = array();

                        $attributes = $customAttrs = array();

                        //First check for the parent product - High priority

                        if (isset($product) && sizeof($product->getData()) > 0) {
                            $customAttrs = $this->getGXpressAttributes(
                                $product, $profile,
                                array(
                                    'required' => false, 'mapped' => true, 'validation' => true
                                )
                            );

                            $attributes = $this->getGXpressAttributes(
                                $product, $profile,
                                array(
                                    'required' => false, 'mapped' => true, 'validation' => false
                                )
                            );
                        }

                        //Second check for the child product - Low priority

                        if (!$attributes) {
                            $customAttrs = $this->getGXpressAttributes(
                                $product->getId(), $profile,
                                array(
                                    'required' => false, 'mapped' => true, 'validation' => true
                                )
                            );
                            $attributes = $this->getGXpressAttributes(
                                $product->getId(), $profile,
                                array(
                                    'required' => false, 'mapped' => true, 'validation' => false
                                ));
                        }

                        $randomCounter = 0;

                        foreach ($customAttrs as $k => $customAttr) {
                            if ($customAttr['magento_attribute_code'] == 'default') {
                                $product->setData($randomCounter . '_ced', $customAttr['default']);
                                $attributes[$customAttr['gxpress_attribute_name']] = $randomCounter . '_ced';
                                $randomCounter++;
                            }
                        }
                        $productToUpload = array();
                        $errorsForChild = false;
                        if($product->getTypeId() == "configurable") {
                            $productArray = $product->toArray();
                            $childArray = array();
                            $productType = $product->getTypeInstance();
                            $configProd = $productType->getUsedProducts($product);

                            foreach ($configProd as $childprod) {
                                $child = $this->objectManager->create(\Magento\Catalog\Model\Product::class)->load($childprod->getId());
                                $childArray = $child->toArray();
                                $productIdentifier = array();

                                if (isset($childArray[$attributes['productId']]) &&
                                    !empty($childArray[$attributes['productId']])) {
                                    /** @var Ced_Googleexpress_Helper_Barcode $barcode */
                                    $barcode = $this->objectManager->create('Ced\GXpress\Helper\Barcode');
                                    $barcode->setBarcode(
                                        $childArray[$attributes['productId']]
                                    );
                                    $productIdentifier = array(
                                        'productIdentifier' => array(
                                            'productIdType' => $barcode->getType(),
                                            'productId' => $barcode->getBarcode(),
                                        )
                                    );
                                }

                                if (isset($product) && sizeof($product->getData()) > 0) {
                                    $childArray[$attributes['imageLink']] = $product->getData($attributes['imageLink']);
                                }
                                $url = array();
                                foreach ($childArray['media_gallery']['images'] as $key => $value) {
                                        $imagePath = $this->_urlBuilder->getBaseUrl() . 'pub/media/catalog/product' . $value['file'];
                                        $url[] = str_replace('index.php/','',$imagePath);
                                                
                                }
                                /*foreach ($child->getMediaGalleryImages() as $gallery) {
                                    $url[] = $gallery->getUrl();
                                }*/
                                //$url = $this->_urlBuilder->getBaseUrl() . 'pub/media/catalog/product' . $childArray['image'];

                                $imageUrl = isset($url[0]) ? $url[0] : '';

                                $product->setData('image', $imageUrl);
                                $attributes['imageLink'] = $imageUrl;

                                if (empty($childprod->getData($attributes['description']))) {
                                    $childprod->setData(
                                        $attributes['description'],
                                        '<![CDATA[' . substr($product->getData($attributes['description']), 0, 3999) . ']]>'
                                    );
                                } else {
                                    $childprod->setData(
                                        $attributes['description'],
                                        '<![CDATA[' . substr($product->getData($attributes['description']), 0, 3999) . ']]>'
                                    );
                                }

                                $childArray = $childprod->toArray();

                                foreach ($attributes as $googleexpress_attribute_name => $magento_attribute_code) {
                                    if ($googleexpress_attribute_name == "imageLink") {
                                        $productToUpload[$childprod->getId()][$googleexpress_attribute_name] = $imageUrl;
                                        continue;
                                    }
                                    if ($googleexpress_attribute_name == "price") {
                                        $currencysymbol = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface');
                                        $productToUpload[$childprod->getId()][$googleexpress_attribute_name]['value'] = $childArray[$magento_attribute_code];
                                        $productToUpload[$childprod->getId()][$googleexpress_attribute_name]['currency'] = $currencysymbol->getStore()->getCurrentCurrencyCode();
                                        continue;
                                    }

                                    if ($googleexpress_attribute_name == "shippingWeight" && $product->getWeight()) {
                                        $productToUpload[$googleexpress_attribute_name]['value'] = $product->getWeight();
                                        $productToUpload[$googleexpress_attribute_name]['unit'] = 'lbs';
                                        continue;
                                    }

                                    $productToUpload[$childprod->getId()][$googleexpress_attribute_name] = strtolower($this->getMappedAttributeValue($magento_attribute_code,$child, $product));
                                    /*$productToUpload[$childprod->getId()][$googleexpress_attribute_name] = isset($childArray[$magento_attribute_code])
                                        ? $childArray[$magento_attribute_code] : (isset($productArray[$magento_attribute_code])
                                            ? $productArray[$magento_attribute_code] : '');*/

//                                    $productToUpload[$googleexpress_attribute_name] = $this->getMappedAttributeValue($magento_attribute_code,$product);
                                    /*$productToUpload['productIdType'] = $productIdentifier['productIdentifier']['productIdType'];*/
                                }
                                
                                $color = isset($attributes['color']) ? $attributes['color'] : null;
                                if($color) {
                                $productToUpload[$childprod->getId()]['link'] = $this->_urlBuilder->getBaseUrl() . $productArray['url_key'] . '.html#93='.$child->getData($color);    
                                } else {
                                    $productToUpload[$childprod->getId()]['link'] = $this->_urlBuilder->getBaseUrl() . $productArray['url_key'];
                                }
                                
                                $stock = $this->objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
                                $qty = $stock->getStockQty($childprod->getId(), $product->getStore()->getWebsiteId());
                                if ($qty) {
                                    $productToUpload[$childprod->getId()]['sellOnGoogleQuantity'] = $qty;
                                    $productToUpload[$childprod->getId()]['availability'] = 'in stock';
                                } else {
                                    $productToUpload[$childprod->getId()]['sellOnGoogleQuantity'] = 0;
                                    $productToUpload[$childprod->getId()]['availability'] = 'out of stock';
                                }
                                
                                foreach ($attr as $googleexpress_attribute_name => $magento_attribute) {

                                $productToUpload[$childprod->getId()][$magento_attribute] = strtolower($this->getMappedAttributeValue($magento_attribute,$child, $product));
                                 
                                    if ($magento_attribute == "additionalImageLinks") {
                                        $productToUpload[$childprod->getId()][$magento_attribute] = [];
                                        if (isset($productArray['media_gallery']['images'])
                                            && !empty(isset($productArray['media_gallery']['images']))) {
                                            foreach ($productArray['media_gallery']['images'] as $key => $value) {
                                                $imagePath = $this->_urlBuilder->getBaseUrl() . 'pub/media/catalog/product' . $value['file'];
                                                $productToUpload[$childprod->getId()][$magento_attribute][] = str_replace('index.php/','',$imagePath);
                                            }
                                        } /*else {
                                            //$productKey = '';
                                            if (isset($childArray['media_gallery']['images'])
                                                && !empty(isset($childArray['media_gallery']['images']))) {
                                                foreach ($productArray['media_gallery']['images'] as $productKey => $value) {
                                                    $imagePath = $this->_urlBuilder->getBaseUrl() . 'pub/media/catalog/product' . $value['file'];
                                                    $productToUpload[$childprod->getId()][$magento_attribute][] = str_replace('index.php/','',$imagePath);
                                                }
                                            
                                                $arrKey = sizeof($productToUpload[$childprod->getId()][$magento_attribute]);
                                                $count = 0;
                                                foreach ($childArray['media_gallery']['images'] as $key => $value) {
                                                    $imagePath = $this->_urlBuilder->getBaseUrl() . 'pub/media/catalog/product' . $value['file'];
                                                    $productToUpload[$childprod->getId()][$magento_attribute][$arrKey+$count] = str_replace('index.php/','',$imagePath);
                                                
                                                }

                                            }
                                        }*/
                                        continue;
                                    }
                                    if ($magento_attribute == "itemGroupId") {
                                        $productToUpload[$childprod->getId()][$magento_attribute] = isset($productArray['sku']) ? $productArray['sku'] : $childArray['sku'];
                                        continue;
                                    }
                                    /*if ($magento_attribute == "color") {
                                        $productToUpload[$childprod->getId()]['color'] = isset($childArray[$magento_attribute]) ? $childArray[$magento_attribute] : '';
                                         continue;
                                    }*/
                                    if ($magento_attribute == "googleProductCategory") {
                                        $productToUpload[$childprod->getId()][$magento_attribute] = $googleProductCategory;
                                        continue;
                                    }
                                    if ($magento_attribute == "shippingWeight" && $childprod->getWeight()) {
                                        $productToUpload[$childprod->getId()][$magento_attribute] = [];
                                        $productToUpload[$childprod->getId()][$magento_attribute]['value'] = $childprod->getWeight();
                                        $productToUpload[$childprod->getId()][$magento_attribute]['unit'] = 'lbs';
                                         continue;
                                    }
                                    if ($magento_attribute == "condition") {
                                        if (isset($childArray['gxpress_condition'])) {
                                            $productToUpload[$childprod->getId()][$magento_attribute] = isset($childArray[$magento_attribute]) ?
                                                $childArray[$magento_attribute] : (isset($productArray[$magento_attribute])
                                                    ? $productArray[$magento_attribute] : '');
                                        }
                                        continue;
                                    }
                                    $productToUpload[$childprod->getId()][$magento_attribute] = isset($childArray[$magento_attribute]) ?
                                                $childArray[$magento_attribute] : (isset($productArray[$magento_attribute])
                                                    ? $productArray[$magento_attribute] : '');


                                    if (empty($productToUpload[$childprod->getId()][$magento_attribute])) {
                                        unset($productToUpload[$childprod->getId()][$magento_attribute]);
                                    }

                                    /*if (isset($productToUpload[$childprod->getId()][$magento_attribute])
                                        && !empty($productToUpload[$childprod->getId()][$magento_attribute])) {
                                        continue;
                                    }
                                    if (isset($productArray[$magento_attribute]) && $productArray[$magento_attribute] != '') {
                                        $productToUpload[$childprod->getId()][$magento_attribute] = isset($productArray[$magento_attribute]) ?
                                            $productArray[$magento_attribute] : '';
                                    }*/
                                }
                                
                                $productToUpload[$childprod->getId()]['targetCountry'] = $this->scopeConfig->getValue(self::CONFIG_PATH_PRODUCT_TARGETCOUNTRY);
                                $productToUpload[$childprod->getId()]['contentLanguage'] = $this->scopeConfig->getValue(self::CONFIG_PATH_PRODUCT_CONTENTLANGUAGE);
                                $productToUpload[$childprod->getId()]['destinations'] = $destination;

                                if (isset($errorsForChild[$product->getSku()])) {
                                    unset($errorsForChild[$product->getSku()]);
                                }
                                if (empty($errorsForChild)) {
                                    $errorsForChild = '["valid"]';
                                }
                                if (count($productToUpload[$childprod->getId()]) <= 1) {
                                    return $returnData;
                                }
                                try {
                                    $returnData = $this->objectManager->get('Ced\GXpress\Helper\GXpresslib')
                                        ->uploadProductOnGXpress($productToUpload[$childprod->getId()]);
                                    if (is_bool($returnData)) {
                                        $response['error']['sku'][] = $product->getSku();
                                    } else if (get_class($returnData) == 'Google_Service_ShoppingContent_ProductsCustomBatchResponse') {
                                        $response['success']['sku'][] = isset($returnData['entries'][0]['product']['offerId'])
                                            ? $returnData['entries'][0]['product']['offerId'] : '';
                                        $this->updateStatus($childprod->getId(), 4);
                                    }
                                } catch (\Exception $e) {
                                    $this->logger("Google Express", "upload Product",
                                        json_encode($returnData), 1);
                                    continue;
                                }
                            }
                            $this->updateStatus($product->getId(), 4);
                        } else {
                            foreach ($product->getMediaGalleryImages() as $gallery) {
                            $url[] = $gallery->getUrl();
                            }
                            $productArray = $product->toArray();
                            $productIdentifier = array();

                            if (isset($productArray[$attributes['productId']]) &&
                                !empty($productArray[$attributes['productId']])) {
                                /** @var Ced_Googleexpress_Helper_Barcode $barcode */
                                $barcode = $this->objectManager->create('Ced\GXpress\Helper\Barcode');
                                $barcode->setBarcode(
                                    $productArray[$attributes['productId']]
                                );

                                $productIdentifier = array(
                                    'productIdentifier' => array(
                                        'productIdType' => $barcode->getType(),
                                        'productId' => $barcode->getBarcode(),
                                    )
                                );
                            }

                            //$url = $this->_urlBuilder->getBaseUrl() . 'pub/media/catalog/product' . $productArray['image'];

                            $imageUrl = isset($url[0]) ? $url[0] : '';

                            $product->setData('image', $imageUrl);
                            $attributes['imageLink'] = $imageUrl;

                            $product->setData(
                                $attributes['description'],
                                '<![CDATA[' . substr($product->getData($attributes['description']), 0, 3999) . ']]>'
                            );
                            $productArray = $product->toArray();

                            foreach ($attributes as $googleexpress_attribute_name => $magento_attribute_code) {

                                if ($googleexpress_attribute_name == "imageLink") {
                                    $productToUpload[$googleexpress_attribute_name] = $imageUrl;
                                    continue;
                                }
                                if ($googleexpress_attribute_name == "price") {
                                    $currencysymbol = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface');
                                    $productToUpload[$googleexpress_attribute_name]['value'] = $productArray[$magento_attribute_code];
                                    $productToUpload[$googleexpress_attribute_name]['currency'] = $currencysymbol->getStore()->getCurrentCurrencyCode();
                                    continue;
                                }

                                if ($googleexpress_attribute_name == "shippingWeight" && $product->getWeight()) {
                                    $productToUpload[$googleexpress_attribute_name]['value'] = $product->getWeight();
                                    $productToUpload[$googleexpress_attribute_name]['unit'] = 'pound';
                                    continue;
                                }
                                //isset($productArray[$magento_attribute_code])? $productArray[$magento_attribute_code] : '';

                                if ($googleexpress_attribute_name == "productId") {
                                    $productToUpload['productIdType'] = $productIdentifier['productIdentifier']['productIdType'];
                                    $productToUpload['productId'] = $productIdentifier['productIdentifier']['productId'];
                                    continue;
                                }
                                $productToUpload[$googleexpress_attribute_name] = $this->getMappedAttributeValue($magento_attribute_code,$product);

                            }

                            if($productArray['visibility'] != 4) {
                                $confProduct = $this->objectManager->
                                create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($product->getId());
                                if(isset($confProduct[0])) {
                                    $confProduct = $this->objectManager->
                                    create('Magento\Catalog\Model\Product')->load($confProduct[0]);
                                    $productToUpload['link'] = $confProduct->getProductUrl();
                                    $productToUpload['mobileLink'] = $confProduct->getProductUrl();
                                }
                            } else{
                                $productToUpload['link'] = $this->_urlBuilder->getBaseUrl() . $productArray['url_key'] . '.html';
                                $productToUpload['mobileLink'] = $this->_urlBuilder->getBaseUrl() . $productArray['url_key'] . '.html';
                            }
                            
                            // $productToUpload['link'] = $this->_urlBuilder->getBaseUrl() . $productArray['url_key'] . '.html';
                            $stock = $this->objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
                            $qty = $stock->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
                            if ($qty) {
                                $productToUpload['sellOnGoogleQuantity'] = $qty;
                                $productToUpload['availability'] = 'in stock';
                            } else {
                                $productToUpload['sellOnGoogleQuantity'] = 0;
                                $productToUpload['availability'] = 'out of stock';
                            }

                            foreach ($attr as $googleexpress_attribute_name => $magento_attribute) {
                                if (isset($productToUpload[$magento_attribute]) && !empty($productToUpload[$magento_attribute])) {
                                    continue;
                                }
                                if ($magento_attribute == "additionalImageLinks") {
                                    /*foreach ($productArray['media_gallery']['images'] as $key => $value) {
                                        $productToUpload[$magento_attribute][] = $this->_urlBuilder->getBaseUrl() . 'pub/media/catalog/product' . $value['file'];
                                    }*/
                                    foreach ($url as $key => $value) {
                                        $productToUpload[$magento_attribute][] = $value;
                                    }
                                    continue;
                                }
                                if ($magento_attribute == "itemGroupId") {
                                    //$productToUpload[$magento_attribute] = isset($productArray['entity_id']) ? $productArray['entity_id'] : '';
                                    continue;
                                }
                                if ($magento_attribute == "color") {
                                    $productToUpload['color'] = isset($productArray[$magento_attribute]) ? $productArray[$magento_attribute] : '';
                                    continue;
                                }
                                if ($magento_attribute == "googleProductCategory") {
                                    $productToUpload[$magento_attribute] = $googleProductCategory;
                                    continue;
                                }
                                if ($magento_attribute == "shippingWeight" && $product->getWeight()) {
                                    $productToUpload[$magento_attribute]['value'] = $product->getWeight();
                                    $productToUpload[$magento_attribute]['unit'] = 'pound';
                                    continue;
                                }
                                if ($magento_attribute == "condition") {
                                    if (isset($productArray['gxpress_condition'])) {
                                        $productToUpload[$magento_attribute] = isset($productArray[$magento_attribute]) ?
                                            $productArray[$magento_attribute] : '';
                                    }
                                    continue;
                                }

                                if (isset($productArray[$magento_attribute]) && $productArray[$magento_attribute] != '') {
                                    $productToUpload[$magento_attribute] = isset($productArray[$magento_attribute]) ?
                                        $productArray[$magento_attribute] : '';
                                    continue;
                                }

                                $productToUpload[$magento_attribute] = $this->getMappedAttributeValue($magento_attribute,$product);

                                if (empty($productToUpload[$magento_attribute])) {
                                    unset($productToUpload[$magento_attribute]);
                                }
                            }

                            $productToUpload['targetCountry'] = $this->scopeConfig->getValue(self::CONFIG_PATH_PRODUCT_TARGETCOUNTRY);
                            $productToUpload['contentLanguage'] = $this->scopeConfig->getValue(self::CONFIG_PATH_PRODUCT_CONTENTLANGUAGE);
                            $productToUpload['destinations'] = $destination;

                            if (isset($errorsForChild[$product->getSku()])) {
                                unset($errorsForChild[$product->getSku()]);
                            }
                            if (empty($errorsForChild)) {
                                $errorsForChild = '["valid"]';
                            }
                            if (count($productToUpload) <= 1) {
                                return $returnData;
                            }
                            try {
                                $returnData = $this->objectManager->get('Ced\GXpress\Helper\GXpresslib')
                                    ->uploadProductOnGXpress($productToUpload);
                                $errors = array();
                                if (is_bool($returnData)) {
                                    $response['error']['sku'][] = $product->getSku();
                                } else if (get_class($returnData) == 'Google_Service_ShoppingContent_ProductsCustomBatchResponse') {
                                    $response['success']['sku'][] = isset($returnData['entries'][0]['product']['offerId'])
                                        ? $returnData['entries'][0]['product']['offerId'] : '';
                                    if(isset($returnData['entries'][0]['errors']['errors'])) {
                                        foreach ($returnData['entries'][0]['errors']['errors'] as $errorKey => $errorValue) {
                                            $errors = $errorValue['message'];
                                        }
                                        $errors[$product->getSku()] = $errors;
                                    }

                                    // $product->setData('gxpress_listing_error_'.$accId,json_encode($errors))->save();
                                    $this->updateAttribute($product, 'gxpress_listing_error_'.$accId, json_encode($errors));
                                        
                                    if(empty($response['success']['sku'][0])) {
                                        $response['error']['sku'][] = $product->getSku();
                                        $response['success'] = array();
                                        $this->updateStatus($product->getId(), 2);
                                    } else {
                                        $this->updateStatus($product->getId(), 4);
                                    }

                                }
                            } catch (\Exception $e) {
                                $this->logger("Google Express", "upload Product",
                                    json_encode($returnData), 1);
                                continue;
                            }

                        }

                        /*if (isset($attrData['targetCountry']) && $attrData['targetCountry'] == 'US') {
                            $attrData['shipping'] =
                                array(
                                    "country" => $attrData['targetCountry'],
                                    "service" => '',
                                    "region" => '',
                                    "price" => array(
                                        "value" => $this->getStoreConfig('carriers/flatrate/price'),
                                        "currency" => $this->storeManager->getStore()->getCurrentCurrency()->getCode()
                                    )
                                );

                        }*/

                        /*$attrData['tax']['country'] = isset($productToUpload['targetCountry']) ? $productToUpload['targetCountry'] : '';
                        $attrData['tax']['rate'] = $this->getStoreConfig('carriers/flatrate/price');
                        $attrData['tax']['taxShip'] = true;*/
                        //$attrData['tax']['region'] = "MA";

                    }
                }
                return $response;
            }
        }
        return $returnData;
    }

    public function getMappedAttributeValue($magentoAttribute, $product, $parentProduct = null)
    {
        if (isset($magentoAttribute) && !empty($magentoAttribute)) {
            if ($product->getData($magentoAttribute) == "" && $parentProduct!= null && $parentProduct->getData($magentoAttribute) == "") {
                return NULL;
            }

            $attr = $product->getResource()->getAttribute($magentoAttribute);
            if ($attr && ($attr->usesSource() || $attr->getData('frontend_input') == 'select')) {
                if ($magentoAttribute == "quantity_and_stock_status") {
                    return $product->getData($magentoAttribute);
                }
                $productAttributeValue = $attr->getSource()->getOptionText($product->getData($magentoAttribute));

            } else {
                $productAttributeValue = $product->getData($magentoAttribute);
            }

            if($parentProduct != null && $productAttributeValue == "") {
                $attr = $parentProduct->getResource()->getAttribute($magentoAttribute);
                if ($attr && ($attr->usesSource() || $attr->getData('frontend_input') == 'select')) {
                    if ($magentoAttribute == "quantity_and_stock_status") {
                        return $product->getData($magentoAttribute);
                    }
                    $productAttributeValue = $attr->getSource()->getOptionText($parentProduct->getData($magentoAttribute));

                } else {
                    $productAttributeValue = $parentProduct->getData($magentoAttribute);
                }

                return $productAttributeValue;
            }
            return $productAttributeValue;
        } else {
            if($parentProduct != null && $product->getData($magentoAttribute) == "") {
                return $parentProduct->getData($magentoAttribute);
            }
            return $product->getData($magentoAttribute);
        }
    }
    
    /*public function getMappedAttributeValue($magentoAttribute, $product)
    {
        if (isset($magentoAttribute) && !empty($magentoAttribute)) {
            if ($product->getData($magentoAttribute) == "") {
                return NULL;
            }

            $attr = $product->getResource()->getAttribute($magentoAttribute);
            if ($attr && ($attr->usesSource() || $attr->getData('frontend_input') == 'select')) {
                if ($magentoAttribute == "quantity_and_stock_status") {
                    return $product->getData($magentoAttribute);
                }
                $productAttributeValue = $attr->getSource()->getOptionText($product->getData($magentoAttribute));

            } else {
                $productAttributeValue = $product->getData($magentoAttribute);
            }
            return $productAttributeValue;
        } else {
            return $product->getData($magentoAttribute);
        }
    }*/

    public function logger(
        $type = "Test",
        $subType = "Test",
        $response = array(),
        $comment = "",
        $forcedLog = false
    )
    {
        if ($this->debugMode || $forcedLog) {
            $this->objectManager->get('Ced\GXpress\Helper\Logger')
                ->addError($type . $response, ['path' => __METHOD__]);
            return true;
        }

        return false;
    }

    public function updateAttribute($product, $code, $value, $accountStoreId = null)
    {
        // Saving in default store i.e admin store id = 0.

        if (!$accountStoreId) {
            $accountStoreId = $this->storeManager->getStore()->getId();
        }

        $product->addAttributeUpdate(
            $code,
            $value,
            $accountStoreId
        );

        // Saving mapped store in case it is different.
        if ($this->storeManager->getStore()->getId() != $accountStoreId) {
            $product->addAttributeUpdate(
                $code,
                $value,
                $this->storeManager->getStore()->getId()
            );
        }

    }

    public function updateStatus(
        $productIds = array(),
        $status = 1
    )
    {
        if (is_string($productIds)) {
            $productIds = array($productIds);
        }

        try {
            $account = $this->_coreRegistry->registry('gxpress_account');
            $accountId = ($account) ? $account->getId() : '';
            $prodStatusAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($accountId);
            $productIds = array_unique($productIds);
            $productAction = $this->objectManager->get('Magento\Catalog\Model\Product\Action');
            $productAction->updateAttributes(
                $productIds,
                array($prodStatusAttr => $status),
                $this->storeManager->getStore()->getId()
            );

            if ($this->storeManager->getStore()->getId() != $this->storeManager->getStore()->getId()) {
                $productAction->updateAttributes(
                    $productIds,
                    array($prodStatusAttr => $status),
                    $this->storeManager->getStore()->getId()
                );
            }
        } catch (\Exception $e) {
            $this->logger('Product status update.', 'Failure', $e->getMessage(), $e->getTraceAsString());
        }

    }

    public function getGXpressAttributes($productId, $profile, $params =
    array('required' => true, 'mapped' => false, 'validation' => false)
    )
    {
        if ($productId) {

            if (empty($profile) || (isset($profile['profile_status']) && !$profile['profile_status'])) {
                return false;
            }

            $profileData = json_decode($profile->getData('profile_cat_attribute'), true);

            $googleexpressAttributes = isset($profileData['required_attributes']) ?
                $profileData['required_attributes'] : array();

            if (isset($params['required']) && $params['required'] == false) {
                $googleexpressAttributes = array_merge($googleexpressAttributes, $profileData['optional_attributes']);
            }

            if ($params['validation'] == true) {
                $attributes = $googleexpressAttributes;
            } else {
                foreach ($googleexpressAttributes as $value) {
                    $attributes[$value['gxpress_attribute_name']] = $value['magento_attribute_code'];
                }
            }
            return $attributes;
        }

        return false;
    }

    /**
     * @return array|bool|mixed|string
     */
    /*public function returnPolicyValue()
    {
        $locationName = '';
        $locationList = $this->location->toOptionArray();
        foreach ($locationList as $value) {
            if ($value['value'] == $this->siteID) {
                $locationName = $value['label'];
            }
        }
        $folderPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('ced/gxpress/');
        $path = $folderPath . $locationName . '/returnPolicy.json';
        if (file_exists($folderPath . $locationName)) {
            $data = $this->loadFile($path, '', '');
        } else {
            $data = [];
        }
        return $data;
    }*/

    /**
     * @param $type
     * @param null $env
     * @return string
     */
    /*public function getUrl($type, $env = null)
    {
        $env = $env == null ? $this->environment : $env;
        if ($env == "production") {
            switch ($type) {
                case 'server':
                    return "https://api.gxpress.com/ws/api.dll";
                    break;

                case 'login':
                    return "https://signin.gxpress.com/ws/gxpressISAPI.dll";
                    break;

                case 'finding':
                    return "http://svcs.gxpress.com/services/search/FindingService/v1";
                    break;

                case 'shopping':
                    return "http://open.api.gxpress.com/shopping";
                    break;

                case 'feedback':
                    return "http://feedback.gxpress.com/ws/gxpressISAPI.dll";
                    break;

                default:
                    return "https://api.gxpress.com/ws/api.dll";
                    break;
            }
        } else {
            switch ($type) {
                case 'server':
                    return "https://api.sandbox.gxpress.com/ws/api.dll";
                    break;

                case 'login':
                    return "https://signin.sandbox.gxpress.com/ws/gxpressISAPI.dll";
                    break;

                case 'finding':
                    return "http://svcs.sandbox.gxpress.com/services/search/FindingService/v1";
                    break;

                case 'shopping':
                    return "http://open.api.sandbox.gxpress.com/shopping";
                    break;

                case 'feedback':
                    return "http://feedback.sandbox.gxpress.com/ws/gxpressISAPI.dll";
                    break;

                default:
                    return "https://api.sandbox.gxpress.com/ws/api.dll";
                    break;
            }
        }
    }*/

    /**
     * @param $siteId
     * @return array
     */
    /*public function getGXpresssites($siteId)
    {
        $site = [];
        switch ($siteId) {
            case '0':
                $site['name'] = "US";
                $site['currency'] = ['USD'];
                $site['abbreviation'] = "US";
                break;
            case '2':
                $site['name'] = "Canada";
                $site['currency'] = ['CAD', 'USD'];
                $site['abbreviation'] = "CA";
                break;
            case '3':
                $site['name'] = "UK";
                $site['currency'] = ['GBR'];
                $site['abbreviation'] = "GB";
                break;
            case '15':
                $site['name'] = "Australia";
                $site['currency'] = ['AUD'];
                $site['abbreviation'] = "AU";
                break;
            case '16':
                $site['name'] = "Austria";
                $site['currency'] = ['EUR'];
                $site['abbreviation'] = "AT";
                break;
            case '23':
                $site['name'] = "Belgium_French";
                $site['currency'] = ['EUR'];
                $site['abbreviation'] = "BEFR";
                break;
            case '71':
                $site['name'] = "France";
                $site['currency'] = ['EUR'];
                $site['abbreviation'] = "FR";
                break;
            case '77':
                $site['name'] = "Germany";
                $site['currency'] = ['EUR'];
                $site['abbreviation'] = "DE";
                break;
            case '101':
                $site['name'] = "Italy";
                $site['currency'] = ['EUR'];
                $site['abbreviation'] = "IT";
                break;
            case '123':
                $site['name'] = "Belgium_Dutch";
                $site['currency'] = ['EUR'];
                $site['abbreviation'] = "BENL";
                break;
            case '146':
                $site['name'] = "Netherlands";
                $site['currency'] = ['EUR'];
                $site['abbreviation'] = "NL";
                break;
            case '186':
                $site['name'] = "Spain";
                $site['currency'] = ['EUR'];
                $site['abbreviation'] = "ES";
                break;
            case '193':
                $site['name'] = "Switzerland";
                $site['currency'] = ['CHF'];
                $site['abbreviation'] = "CH";
                break;
            case '201':
                $site['name'] = "HongKong";
                $site['currency'] = ['HKD'];
                $site['abbreviation'] = "HK";
                break;
            case '203':
                $site['name'] = "India";
                $site['currency'] = ['INR'];
                $site['abbreviation'] = "IN";
                break;
            case '205':
                $site['name'] = "Ireland";
                $site['currency'] = ['EUR'];
                $site['abbreviation'] = "IE";
                break;
            case '207':
                $site['name'] = "Malaysia";
                $site['currency'] = ['MYR'];
                $site['abbreviation'] = "MY";
                break;
            case '210':
                $site['name'] = "CanadaFrench";
                $site['currency'] = ['CAD', 'USD'];
                $site['abbreviation'] = "CAFR";
                break;
            case '211':
                $site['name'] = "Philippines";
                $site['currency'] = ['PHP'];
                $site['abbreviation'] = "PH";
                break;
            case '212':
                $site['name'] = "Poland";
                $site['currency'] = ['PLN'];
                $site['abbreviation'] = "PL";
                break;
            case '216':
                $site['name'] = "Singapore";
                $site['currency'] = ['SGD'];
                $site['abbreviation'] = "SG";
                break;
            default:
                $site = [];
                break;
        }
        return $site;
    }*/

    public function responseParse($response = '', $type = null, $filePath = '')
    {
        if ($type) {
            try {
                $accountId = 0;
                $currentAccount = $this->_coreRegistry->registry('gxpress_account');
                if ($currentAccount) {
                    $accountId = $currentAccount->getId();
                }
                $feedModel = $this->objectManager->create('\Ced\GXpress\Model\Feeds');
                $feedModel->setData('feed_date', date('Y-m-d H:i:s'));
                $feedModel->setData('feed_type', $type);
                $feedModel->setData('feed_source', isset($response->Ack) ? $response->Ack : 'Unknown');
                $feedModel->setData('feed_errors', $this->json->jsonEncode($response));
                $feedModel->setData('feed_file', $filePath);
                $feedModel->setData('account_id', $accountId);
                $feedModel->save();
                return true;
            } catch (\Exception $e) {
                return false;
            }

        }
        return true;
    }

    /**
     * @param string $name
     * @param string $code
     * @return array|string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createDir($name = 'gxpress', $code = 'var')
    {
        $path = $this->directoryList->getPath($code) . "/" . $name;
        if (file_exists($path)) {
            return ['status' => true, 'path' => $path, 'action' => 'dir_exists'];
        } else {
            try {
                $this->fileIo->mkdir($path, 0775, true);
                return ['status' => true, 'path' => $path, 'action' => 'dir_created'];
            } catch (\Exception $e) {
                return $code . '/' . $name . "Directory Creation Failed.";
            }
        }
    }

    /*public function createFeed($finalData = null, $variable)
    {
        $path = $this->createDir('gxpress/' . $variable, 'media');
        $path = $path['path'] . '/' . $variable . '_' . $this->timestamp . '.xml';
        $handle = fopen($path, 'w');
        $finalData = preg_replace('/(\<\?xml\ version\=\"1\.0\"\?\>)/', '<?xml version="1.0" encoding="UTF-8"?>',
            $finalData);
        fwrite($handle, htmlspecialchars_decode($finalData));
        fclose($handle);
        return $path;
    }*/

    /**
     * @param $responseXml
     * @return mixed
     */
    /*public function ParseResponse($responseXml)
    {
        $sxe = new \SimpleXMLElement ($responseXml);
        return $res = json_decode(json_encode($sxe));
    }*/

    public function setAccountSession()
    {
        $accountId = '';
        $this->adminSession->unsAccountId();
        $params = $this->_getRequest()->getParams();
        if (isset($params['account_id']) && $params['account_id'] > 0) {
            $accountId = $params['account_id'];
        } else {
            $accountId = $this->scopeConfig->getValue('gxpress_config/gxpress_setting/primary_account');
            if (!$accountId) {
                $accounts = $this->multiAccountHelper->getAllAccounts();
                if ($accounts) {
                    $accountId = $accounts->getFirstItem()->getId();
                }
            }
        }
        $this->adminSession->setAccountId($accountId);
        return $accountId;
    }

    public function getAccountSession()
    {
        $accountId = '';
        $accountId = $this->adminSession->getAccountId();
        if (!$accountId) {
            $accountId = $this->setAccountSession();
        }
        return $accountId;
    }

    /**
     * @param null $page
     * @return array|string
     */
    /*public function importProduct($page = null)
    {
        $result = [];
        $importFieldMappings[] = array(
            'gxpress_attribute' => 'SKU',
            'magento_attribute' => 'sku'
        );
        $page = empty($page) ? 1 : $page;
        $account = $this->_coreRegistry->registry('gxpress_account');
        $accountId = $account->getId();
        $itemIdAccAttr = $this->multiAccountHelper->getItemIdAttrForAcc($accountId);
        $listingErrorAccAttr = $this->multiAccountHelper->getProdListingErrorAttrForAcc($accountId);
        $prodStatusAccAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($accountId);
        $importFieldMapping = $this->scopeConfig->getValue('gxpress_config/product_upload/import_field_mapping');
        if ($importFieldMapping && $importFieldMapping != null) {
            $importFieldMappings = json_decode($importFieldMapping, true);
        }
        if (empty($this->token)) {
            $result = "Please fetch the token";
        } else {
            $variable = "GetMygxpressSelling";
            $requestBody = '<?xml version="1.0" encoding="utf-8"?>
                            <GetMygxpressSellingRequest xmlns="urn:gxpress:apis:eBLBaseComponents">
                              <RequesterCredentials>
                                <gxpressAuthToken>' . $this->token . '</gxpressAuthToken>
                              </RequesterCredentials>  
                              <ActiveList>
                                <Sort>TimeLeft</Sort>
                                <Pagination>
                                 <EntriesPerPage>100</EntriesPerPage>
                                  <PageNumber>' . $page . '</PageNumber>
                                </Pagination>
                              </ActiveList>
                            </GetMygxpressSellingRequest>';
            $response = $this->sendHttpRequest($requestBody, $variable, 'server');

            if (isset($response->Ack) && $response->Ack == 'Success') {
                if (isset($response->ActiveList->ItemArray)) {
                    foreach ($response->ActiveList->ItemArray->Item as $item) {
                        if (isset($item->SKU) && isset($item->ItemID)) {
                            foreach ($importFieldMappings as $importField) {
                                $product = $this->objectManager->create('Magento\Catalog\Model\Product')->loadByAttribute($importField['magento_attribute'], $item->{$importField['gxpress_attribute']});
                                if ($product)
                                    break;
                            }
                            $gxpressItemId = $item->ItemID;
                            if ($product) {
                                $product->setData($prodStatusAccAttr, 4);
                                $product->setData($listingErrorAccAttr, json_encode(["valid"]));
                                $product->setData($itemIdAccAttr, $gxpressItemId);
                                $product->getResource()->saveAttribute($product, $itemIdAccAttr)->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $listingErrorAccAttr);
                                $successids[] = $product->getSku();
                            } else {
                                $failureids[] = $item->ItemID;
                            }
                        } else {
                            $failureids[] = $item->ItemID;
                        }
                    }
                    $totalQty = $response->ActiveList->PaginationResult->TotalNumberOfEntries;
                    $result['check'] = (int)$totalQty > 100 * $page ? "continue" : '';
                    if (isset($successids) && is_array($successids) && count($successids) > 0) {
                        $result['success'] = "Successfully Imported SKU's" . implode(', ', $successids);
                    } else if (isset($failureids) && is_array($failureids) && count($failureids) > 0) {
                        $result['error'] = "Product not found for Item Ids : " . implode(', ', $failureids);
                    }
                }
            } else {
                $result['error'] = $response->errorMessage;
                $result['check'] = "continue";
            }
        }
        return $result;
    }*/
}
